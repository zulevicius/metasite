<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\SubscriptionType;
use App\Repository\CsvDatabaseManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SubscriptionsController.
 */
class SubscriptionsController extends AbstractController
{
    /**
     * @var CsvDatabaseManager
     */
    private $subscriptionsDb;

    /**
     * @var CsvDatabaseManager
     */
    private $categoriesDb;


    public function __construct(string $subscriptionsFile, string $categoriesFile)
    {
        $this->subscriptionsDb = new CsvDatabaseManager($subscriptionsFile, Subscription::class);
        $this->categoriesDb = new CsvDatabaseManager($categoriesFile, Category::class);
    }

    /**
     *
     * @IsGranted({"ROLE_ADMIN", "ROLE_USER"})
     * @Route("/manageSubscriptions", name="manage_subscriptions")
     */
    public function manageSubscriptions()
    {
        $subs = $this->subscriptionsDb->getAllRecords();
        return $this->render('subscriptions/index.html.twig', ['subscriptions' => $subs]);
    }

    /**
     *
     * @Route("/")
     * @Route("/subscription/add", name="add_subscription")
     */
    public function addSubscription(Request $request)
    {
        $sub = new Subscription();
        $cats = $this->categoriesDb->getAllRecords();
        $form = $this->createForm(SubscriptionType::class, $sub);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sub->setId($this->subscriptionsDb->getEntitiesMaxId());
            $sub->setCategories(implode('|', $_POST['categories'] ?? []));
            $sub->setRegistrationDate(date('Y-m-d H:i'));
            if ($this->subscriptionsDb->saveRecord($sub)) {
                $this->addFlash('success', $form->getData()->getEmail() . ' subscription saved');
            } else {
                $this->addFlash('error', $form->getData()->getEmail() . ' - email already exists');
            }

            $route = $this->getUser() ? 'manage_subscriptions' : 'add_subscription';
            return $this->redirectToRoute($route);
        }

        return $this->render(
            'subscriptions/add.html.twig',
            [
                'form' => $form->createView(),
                'subscription' => $sub,
                'categories' => $cats,
            ]
        );
    }

    /**
     *
     * @IsGranted({"ROLE_ADMIN", "ROLE_USER"})
     * @Route("/subscription/edit/{id}", name="edit_subscription")
     */
    public function editSubscription($id, Request $request)
    {
        $sub = $this->subscriptionsDb->getRecord($id);
        $cats = $this->categoriesDb->getAllRecords();
        $form = $this->createForm(SubscriptionType::class, $sub);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sub->setCategories(implode('|', $_POST['categories'] ?? []));
            $this->subscriptionsDb->updateRecord($sub->primaryKeyVal(), $sub);

            return $this->redirectToRoute('manage_subscriptions');
        }

        return $this->render(
            'subscriptions/edit.html.twig',
            [
                'form' => $form->createView(),
                'subscription' => $sub,
                'categories' => $cats
            ]
        );
    }

    /**
     *
     * @IsGranted({"ROLE_ADMIN", "ROLE_USER"})
     * @Route("/subscription/delete/{id}", name="delete_subscription")
     */
    public function deleteSubscription($id, Request $request)
    {
        if ($this->subscriptionsDb->deleteRecord($id)) {
            $this->addFlash('success', 'Subscription deleted');
        } else {
            $this->addFlash('error', 'Record not found');
        }
        return $this->redirectToRoute('manage_subscriptions');
    }
}
