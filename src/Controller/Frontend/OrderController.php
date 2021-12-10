<?php

namespace App\Controller\Frontend;

use App\DTO\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\Frontend\OrderType;
use App\Form\Frontend\CreditCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/livraison", name="app_frontend_order_delivery")
     */
    public function delivery(Cart $cart): Response
    {
        if(!$this->getUser()->getAddresses()->getValues())
        {
            return $this->redirectToRoute('app_frontend_account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('frontend/order/delivery.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="app_frontend_order_add", methods={"POST"})
     */
    public function add(Cart $cart, Request $request): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $date = new \DateTime();
            $carriers = $form->get('carriers')
                             ->getData();
            $delivery = $form->get('addresses')
                             ->getData();
            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= '<br/>'.$delivery->getAddress();
            $delivery_content .= '<br/>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br/>'.$delivery->getCountry();
            $delivery_content .= '<br/>'.$delivery->getPhone();

            //Register my order : Order
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);

            //Register my products : OrderDetails
            foreach($cart->getFull() as $product)
            {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);

                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();

            return $this->render('frontend/order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
                'id' => $order->getId()
            ]);
        }

        return $this->redirectToRoute('app_frontend_cart_index');
    }

    /**
     * @Route("/commande/paiement/{id}", name="app_frontend_order_pay")
     */
    public function pay(Request $request, Order $order): Response
    {
        $form = $this->createForm(CreditCardType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute('app_frontend_order_confirm', [
                'id' => $order->getId()
            ]);
        }

        return $this->render('frontend/order/pay.html.twig', [
            'form' => $form->createView(),
            'order' => $order
        ]);
    }

    /**
     * @Route("/commande/confirmation/{id}", name="app_frontend_order_confirm")
     */
    public function confirm(Order $order, Cart $cart): Response
    {
        if(!$order || $order->getUser() != $this->getUser())
        {
            return $this->redirectToRoute('app_frontend_home_index');
        }

        if(!$order->getIsPaid())
        {
            $cart->remove();

            $order->setIsPaid(1);
            $this->entityManager->flush();
        }

        return $this->render('frontend/order/success.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * @Route("/commande/erreur/{id}", name="app_frontend_order_error")
     */
    public function error(Order $order): Response
    {
        if(!$order || $order->getUser() != $this->getUser())
        {
            return $this->redirectToRoute('app_frontend_home_index');
        }

        return $this->render('frontend/order/cancel.html.twig', [
            'order' => $order
        ]);
    }
}
