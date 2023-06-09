<?php

declare(strict_types=1);

namespace App\Common\Controller;

use App\Product\Repository\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface             $logger
    ) {
    }

    #[Route(path: "/", name: "home")]
    public function index(): Response
    {
        try {
            $products = $this->productRepository->findAllProducts();
            return $this->render('app/home.html.twig', compact('products'));
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => $e->getMessage()]);
        }
    }
}
