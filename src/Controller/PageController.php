<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Contact;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Form\ContactFormType;


final class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $repository = $doctrine->getRepository(Category::class);
        $categories = $repository->findAll();
        return $this->render('page/index.html.twig', ['categories' => $categories]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig', []);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(ManagerRegistry $doctrine, Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contacto = $form->getData();    
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('thankyou', []);
        }
        return $this->render('page/contact.html.twig', array(
            'form' => $form->createView()    
        ));
    }

    #[Route('/thankyou', name: 'thankyou')]
    public function thankyou(): Response
    {
        return $this->render('page/thankyou.html.twig', []);
    }

    #[Route('/blog/{page}', name: 'blog', requirements: ['page' => '\d+'])]
    public function blogLista(ManagerRegistry $doctrine, int $page = 1): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $posts = $repository->findAll($page);

        return $this->render('page/blog.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/blog/new', name: 'new_post')]
    public function newPost(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute("app_login");
        }
        
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();   
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // El Slugger hace que el nombre del archivo sea seguro en cuanto a 
                // caracteres especiales como espacios o acentos
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                // El servidor almacena el archivo en un directorio temporal y
                // debemos moverlo a su ubicación definitiva, dentro de una ruta que
                // hemos definido en los parámetros de configuración (services.yaml)
                // y que debe existir previamente dentro de la carpeta `public` proyecto
                try {

                    // Primero lo movemos al directorio de imágenes
                    $file->move(
                        $this->getParameter('blog_directory'), $newFilename
                    );
                    $filesystem = new Filesystem();
                    // Y ahora lo duplicamos en el directorio de portfolio
                    $filesystem->copy(
                        $this->getParameter('blog_directory') . '/'. $newFilename, true);

                } catch (FileException $e) {
                    return new Response("Error al subir el archivo: " . $e->getMessage());
                }

                // asignamos el nombre del archivo, que se llama `file`, a la entidad Image
                $post->setImage($newFilename);
            }


            // Quitamos los caracteres especiales del título para crear el slug
            $post->setSlug($slugger->slug($post->getTitle()));
            
            // Guardamos el usuario que crea el post y los contadores a 0
            $post->setPostUser($this->getUser());
            $post->setNumLikes(0);
            $post->setNumComments(0);

            $entityManager = $doctrine->getManager();    
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->render('blog/new_post.html.twig', array(
                'form' => $form->createView()    
            ));
        }
        return $this->render('blog/new_post.html.twig', array(
            'form' => $form->createView()    
        ));
        return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);
    }
    
    #[Route('/single_post/{slug}', name: 'single_post')]
    public function post(ManagerRegistry $doctrine, Request $request, $slug): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $repository2 = $doctrine->getRepository(Comment::class);
        $post = $repository->findOneBy(["slug"=>$slug]);
        $recents = $repository->findRecents();
        $comentarios = $repository2->findAll();
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);  
            //Aumentamos en 1 el número de comentarios del post
            $post->setNumComments($post->getNumComments() + 1);
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);
        }
        return $this->render('blog/single_post.html.twig', [
            'post' => $post,
            'recents' => $recents,
            'comentarios' => $comentarios,
            'commentForm' => $form->createView()
        ]);
    }

    #[Route('/single_post/{slug}/like', name: 'post_like')]
    public function like(ManagerRegistry $doctrine, $slug): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $post = $repository->findOneBy(["slug"=>$slug]);
        if ($post){
            // Haz un método llamado like() en la entidad Post que aumente en 1 numLikes
            $post->like();
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($post);
            $entityManager->flush();
        }
        return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);

    }

    #[Route('/blog/buscar', name: 'blog_buscar')]
    public function buscar(ManagerRegistry $doctrine,  Request $request): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $searchTerm = $request->query->get('searchTerm', '');
        $posts = $repository->findByText($searchTerm);
        $recents = $repository->findRecents();
        return $this->render('page/blog.html.twig', [
            'posts' => $posts,
            'recents' => $recents,
            'searchTerm' => $searchTerm
        ]);
    }

}