<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminerController extends AbstractController
{
    #[Route('/adminer', name: 'app_adminer')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        // Path to Adminer file
        $adminerPath = $this->getParameter('kernel.project_dir') . '/public/adminer.php';

        // Check if file exists
        if (!file_exists($adminerPath)) {
            throw $this->createNotFoundException('Adminer not found');
        }

        // Include and execute Adminer
        ob_start();
        include $adminerPath;
        $content = ob_get_clean();

        return new Response($content);
    }
}