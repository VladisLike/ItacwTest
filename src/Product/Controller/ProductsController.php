<?php

declare(strict_types=1);

namespace App\Product\Controller;

use App\Common\Annotation\Guid;
use App\Product\DTO\ProductCreateDTO;
use App\Product\DTO\ProductEditDTO;
use App\Product\Entity\Product;
use App\Product\Form\ProductCreateForm;
use App\Product\Form\ProductEditForm;
use App\Product\Repository\ProductRepositoryInterface;
use App\Product\Service\Products\ProductsServiceInterface;
use App\Salesman\Entity\Salesman;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'products', name: 'products')]
final class ProductsController extends AbstractController
{
    public function __construct(
        private readonly ProductsServiceInterface $productsService,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface          $logger
    ) {
    }

    #[Route(path: '', name: '', methods: ["GET"])]
    public function index(): Response
    {
        /** @var Salesman $user */
        $user = $this->getUser();
        try {
            $products = $this->productRepository->findProductsBySalesman($user);
            return $this->render('app/product/index.html.twig', compact('products'));
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            return $this->render('app/product/index.html.twig', [
                "products" => []
            ]);
        }
    }

    #[Route(path: "/create", name: ".create")]
    #[IsGranted('ROLE_SALESMAN')]
    public function create(Request $request): Response
    {
        /** @var Salesman $salesman */
        $salesman = $this->getUser();
        $productCreateDTO = new ProductCreateDTO();

        $form = $this->createForm(ProductCreateForm::class, $productCreateDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->productsService->createProduct($productCreateDTO, $salesman);
                return $this->redirectToRoute('home');
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: "/{id}/edit", name: ".edit")]
    #[IsGranted('ROLE_SALESMAN')]
    public function edit(Product $product, Request $request): Response
    {
        $productEditDTO = ProductEditDTO::fromProduct($product);
        $form = $this->createForm(ProductEditForm::class, $productEditDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->productsService->editProduct($productEditDTO);
                return $this->redirectToRoute('products.show', ['id' => $product->getId()]);
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: "/{id}", name: ".show", requirements: ["id" => Guid::PATTERN])]
    public function show(Product $product): Response
    {
        $salesman = $product->getSalesman();
        return $this->render('app/product/show.html.twig', compact('product', 'salesman'));
    }
}
