<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\Validate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class TransactionController extends Controller
{
    /**
     * @Route("/api/transactions/{customerId}/{transactionId}", name="show_transaction")
     * @Method({"GET"})
     */

    public function showTransaction($customerId, $transactionId)
    {
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneBy([
            'customerId'    =>  $customerId,
            'id'            =>  $transactionId
        ]);

        if(empty($transaction))
        {
            $response = array(
                'code'=>1,
                'message'=>'Transaction for the given customer not found',
                'errors'=>null,
                'result'=>null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('jms_serializer')->serialize($transaction, 'json');

        $response = array(
            'code'=>0,
            'message'=>'success',
            'errors'=>null,
            'result'=>json_decode($data)
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/transactions", name="create_transaction")
     * @Method({"POST"})
     */

    public function createTransaction(Request $request)
    {
        $data = $request->getContent();

        $transactions = json_decode($data);

        if(empty($transactions->customerId))
        {
            $response = array(
                'code' => 0,
                'message' => 'Customer Id Can\'t be blank!',
                'errors' => null,
                'result' => null
            );
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }else{
            $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->find($transactions->customerId);

            if(empty($customer))
            {
                $response = array(
                    'code'=>1,
                    'message'=>'No customer found',
                    'error'=>null,
                    'result'=>null
                );

                return new JsonResponse($response, Response::HTTP_NOT_FOUND);
            }
        }

        if(empty($transactions->amount))
        {
            $response = array(
                'code' => 0,
                'message' => 'Amount Can\'t be blank!',
                'errors' => null,
                'result' => null
            );
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        if(!empty($response))
        {
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setCustomerId($transactions->customerId);
        $transaction->setAmount($transactions->amount);
        $transaction->setTransactionDate(new \DateTime(date('Y-m-d H:i:s')));
        $transaction->setOffset($transactions->offset);
        $em->persist($transaction);
        $em->flush();

        $response = array(
            'code' => 0,
            'message' => 'Transaction Added!',
            'errors' => null,
            'result' => null
        );

        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/transactions", name="list_transaction")
     * @Method({"GET"})
     */

    public function listTransaction()
    {
        $transactions = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findAll();

        if(!count($transactions))
        {
            $response = array(
                'code' => 1,
                'message' => 'No Transactions Found!',
                'errors' => null,
                'result' => null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('jms_serializer')->serialize($transactions, 'json');

        $response = array(
            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/api/transactions/{id}", name="update_transaction")
     * @Method({"PUT"})
     * @return JsonResponse
     */

    public function updateTransaction(Request $request, $id, Validate $validate)
    {
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->find($id);

        if(empty($transaction))
        {
            $response = array(
                'code' => 1,
                'message' => 'Transaction not found!',
                'errors' => null,
                'result' => null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $body = $request->getContent();

        $data = $this->get('jms_serializer')->deserialize($body, 'AppBundle\Entity\Transaction', 'json');

        $response = $validate->validateRequest($data);

        if(!empty($response))
        {
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        $transaction->setAmount($data->getAmount());

        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        $response = array(
            'code' => 0,
            'message' => 'Transaction updated',
            'errors' => null,
            'result' => null
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @Route("/api/transactions/{id}", name="delete_post")
     * @Method({"DELETE"})
     */
    public function deleteCustomer($id)
    {
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->find($id);

        if(empty($transaction))
        {
            $response = array(
                'code'=>1,
                'message'=>'Customer Not Found!',
                'errors'=>null,
                'result'=>null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($transaction);
        $em->flush();

        $response=array(
            'code'=>0,
            'message'=>'Customer Deleted!',
            'errors'=>null,
            'result'=>null
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @Route("/api/total_transactions", name="total_transactions")
     */
}
