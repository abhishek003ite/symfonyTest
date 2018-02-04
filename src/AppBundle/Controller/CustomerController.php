<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\Validate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CustomerController extends Controller
{
    /**
     * @Route("/api/customers/{id}", name="show_customer")
     * @Method({"GET"})
     */

    public function showCustomer($id)
    {
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->find($id);

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

        $data = $this->get('jms_serializer')->serialize($customer, 'json');

        $response = array(
            'code'  => 0,
            'message'   =>  'success',
            'errors'    =>  null,
            'result'    =>  json_decode($data)
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/customers", name="create_customer")
     * @Method({"POST"})
     */

    public function createCustomer(Request $request, Validate $validate)
    {
        $data = $request->getContent();

        $customer = $this->get('jms_serializer')->deserialize($data, 'AppBundle\Entity\Customer', 'json');

        $response = $validate->validateRequest($customer);

        if(!empty($response))
        {
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($customer);
        $em->flush();

        $response = array(
            'code'  =>  0,
            'message'   =>  'Customer Added Successfully!',
            'errors'    =>  null,
            'result'    =>  null
        );

        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/customers", name="list_customer")
     * @Method({"GET"})
     */

    public function listCustomer()
    {
        $customers = $this->getDoctrine()->getRepository('AppBundle:Customer')->findAll();

        if(!count($customers))
        {
            $response = array(
                'code'  =>  1,
                'message' => "No customer found!",
                'errors' => null,
                'result' => null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('jms_serializer')->serialize($customers, 'json');

        $response=array(
            'code'=>0,
            'message'=>'success',
            'errors'=>null,
            'result'=>json_decode($data)
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/api/customers/{id}", name="update_customer")
     * @Method({"PUT"})
     * @return JsonResponse
     */
    public function updateCustomer(Request $request, $id, Validate $validate)
    {
        $customer=$this->getDoctrine()->getRepository('AppBundle:Customer')->find($id);

        if(empty($customer))
        {
            $response = array(
                'code'  =>  1,
                'message' => 'Customer Not Found!',
                'errors' => null,
                'result' => null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $body = $request->getContent();

        $data = $this->get('jms_serializer')->deserialize($body, 'AppBundle\Entity\Customer', 'json');

        $response = $validate->validateRequest($data);

        if(!empty($response))
        {
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        $customer->setName($data->getName());
        $customer->setCnp($data->getCnp());

        $em=$this->getDoctrine()->getManager();
        $em->persist($customer);
        $em->flush();

        $response=array(
            'code'=>0,
            'message'=>'Customer Updated!',
            'errors'=>null,
            'result'=>null
        );

        return new JsonResponse($response, 200);
    }

    /**
     * @Route("/api/customers/{id}", name="delete_post")
     * @Method({"DELETE"})
     */

    public function deleteCustomer($id)
    {
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->find($id);

        if(empty($customer))
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
        $em->remove($customer);
        $em->flush();

        $response=array(
            'code'=>0,
            'message'=>'Customer Deleted!',
            'errors'=>null,
            'result'=>null
        );

        return new JsonResponse($response, 200);
    }
}
