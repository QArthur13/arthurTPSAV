<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_index", methods={"GET"})
     */
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="order_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET"})
     */
    public function show(Order $order): Response
    {
        /*$removeFirstChose = str_replace("'[{", '', $order->getListeProduit());
        $removeLastChose = str_replace("}]'", '', $removeFirstChose);
        dd($removeLastChose);*/

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/product/{id}", name="order_product", methods={"GET"})
     * @param Order $order
     * @return void
     */
    public function updateProduct(Order $order)
    {
        $product = (explode( ",", $order->getListeProduit()));
        $id = str_replace("id:", "", $product[0]);
        $quantity = str_replace("q:", "", $product[1]);
        $curlGetId = curl_init();

        curl_setopt($curlGetId, CURLOPT_URL, "http://176.129.113.86:8000/api/products/".$id);
        curl_setopt($curlGetId, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curlGetId);

        curl_close($curlGetId);

        $decode = json_decode($result);
        $newQuantity = $decode->{'quantity'} - $quantity;
        $data = array('quantity' => $newQuantity);
        $curlUpdate = curl_init();

        curl_setopt($curlUpdate, CURLOPT_URL, "http://176.129.113.86:8000/api/products/".$id);
        curl_setopt($curlUpdate, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlUpdate, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curlUpdate, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curlUpdate, CURLOPT_HTTPHEADER, array("Content-Type: application/merge-patch+json"));
        $resultUpdate = curl_exec($curlUpdate);
        curl_close($curlUpdate);
        echo "<pre>";
        print_r(json_decode($resultUpdate));
        echo "</pre>";
        die();
    }

    /**
     * @Route("/{id}/edit", name="order_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="order_delete", methods={"POST"})
     */
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
    }
}
