<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/product', name: 'product_catalog')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
        // $products = $this->productService->getProducts();
        // return $this->render('product/index.html.twig', ['productData' => $products,]);
    }
    
    #[Route('/product/download', name: 'product_catalog_download')]
    public function downloadCsv(): StreamedResponse
    {
        try {
            $productsData = $this->productService->getProducts();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        $response = new StreamedResponse(function() use ($productsData) {
            $handle = fopen('php://output', 'w+');

            // Add the header of the CSV file
            fputcsv($handle, [
                'EAN', 'Item Description', 'Product Primary Category', 'Product Secondary Category',
                'Product Primary Brand', 'Weight', 'Unit of Measure', 'Unit Net Price', 'Unit Retail Price',
                'Tax Rate', 'Item Tax Rate', 'Item Tax Category Code', 'Warranty Length', 'Specifications', 'Pictures'
            ]);

            // Add the data of the CSV file
            foreach ($productsData as $product) {
                fputcsv($handle, [
                    $product['ean'] ?? '',
                    $product['itemDescription'] ?? '',
                    $product['productPrimaryCategory']['categoryNodeName'] ?? '',
                    $product['productSecondaryCategory']['categoryNodeName'] ?? '',
                    $product['productPrimaryBrand']['brandNodeName'] ?? '',
                    $product['weight'] ?? '',
                    $product['unitOfMeasure'] ?? '',
                    $product['unitNetPrice'] ?? '',
                    $product['unitRetailPrice'] ?? '',
                    $product['taxRate'] ?? '',
                    $product['itemTaxRate'] ?? '',
                    $product['itemTaxCategoryCode'] ?? '',
                    $product['warrantyLength'] ?? '',
                    implode(', ', array_map(fn($attr) => $attr['name'] . ': ' . ($attr['values'][0]['displayValue'] ?? ''), $product['specification']['attributes'] ?? [])),
                    implode(', ', array_map(fn($pic) => $pic['url'], $product['pictures'] ?? []))
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="product_catalog.csv"');

        return $response;
    }
}
