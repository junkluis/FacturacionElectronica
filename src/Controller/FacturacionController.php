<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Producto;
use App\Entity\Cliente;
use Doctrine\ORM\EntityManagerInterface;



class FacturacionController extends AbstractController
{
    /**
     * @Route("/facturacion", name="facturacion")
     */
    public function index()
    {
        return $this->render('facturacion/index.html.twig', [
            'controller_name' => 'FacturacionController',
        ]);
    }


    /**
     * @Route("/clientes", name="clientes")
     */
    public function clientes()
    {
        $repository = $this->getDoctrine()->getRepository(Cliente::class);
        $clientes = $repository->findAll();
        return $this->render('facturacion/clientes.html.twig', [
            'controller_name' => 'FacturacionController',
            'clientes' => $clientes
        ]);
    }


    /**
     * @Route("/clientes/nuevoCliente", name="nuevoCliente")
     */
    public function nuevoCliente()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;


        $cliente = new Cliente();

        $cliente->setCiRuc($data->get('ruc'));
        $cliente->setRazonSocial($data->get('razonsocial'));
        $cliente->setTelefono($data->get('telefono'));
        $cliente->setDireccion($data->get('direccion'));
        $em->persist($cliente);
        $em->flush();

        $response->setContent(json_encode([
            'id' => $cliente->getId(),
            'ruc' => $cliente->getCiRuc(),
            'razonsocial' => $cliente->getRazonSocial(),
            'telefono' => $cliente->getTelefono(),
            'direccion' => $cliente->getDireccion(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/clientes/verificarRUC", name="verificarRUC")
     */
    public function verificarCliente()
    {
        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Cliente::class);
        $data = $request->request;
        $ruc = $data->get('ruc');
        $cliente = $repositorio->findOneBy(['ci_ruc' => $ruc]);

        if (!$cliente) {
            $msj = 'disponible';
        } else {
            if($data->get('id') == $cliente->getId()){
                $msj = 'disponible';
            } else {
                $msj = 'ocupado';    
            }
            
        }

        $response->setContent(json_encode([
            'msj' => $msj
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/clientes/eliminarCliente", name="eliminarCliente")
     */
    public function eliminarCliente()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Cliente::class);

        $data = $request->request;
        $id = $data->get('id');
        $cliente = $repositorio->find($id);

        $em->remove($cliente);
        $em->flush();

        $response->setContent(json_encode([
            'eliminado' => 'ok'
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/clientes/editarCliente", name="editarCliente")
     */
    public function editarCliente()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;

        $cliente = $em->getRepository(Cliente::class)->find($data->get('id'));
        $msj = "";

        if (!$cliente) {
            $msj = 'err';
        } else {
            $cliente->setCiRuc($data->get('ruc'));
            $cliente->setRazonSocial($data->get('razonsocial'));
            $cliente->setTelefono($data->get('telefono'));
            $cliente->setDireccion($data->get('direccion'));
            $em->flush();
            $msj = 'Creado con exito';
        }

        $response->setContent(json_encode([
            'id' => $cliente->getId(),
            'ruc' => $cliente->getCiRuc(),
            'razonsocial' => $cliente->getRazonSocial(),
            'telefono' => $cliente->getTelefono(),
            'direccion' => $cliente->getDireccion(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/empresas", name="empresas")
     */
    public function empresas()
    {
        return $this->render('facturacion/empresas.html.twig', [
            'controller_name' => 'FacturacionController',
        ]);
    }


    /**
     * @Route("/productos", name="productos")
     */
    public function productos()
    {
        $repository = $this->getDoctrine()->getRepository(Producto::class);
        $products = $repository->findAll();


        return $this->render('facturacion/productos.html.twig', [
            'controller_name' => 'FacturacionController',
            'productos' => $products
        ]);
    }


    /**
     * @Route("/configuracion", name="configuracion")
     */
    public function configuracion()
    {
        return $this->render('facturacion/configuracion.html.twig', [
            'controller_name' => 'FacturacionController',
        ]);
    }


    /**
     * @Route("/productos/nuevoProducto", name="nuevoProducto")
     */
    public function nuevoProducto()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();

        $data = $request->request;

        if($data->get('impuesto') == "true"){
            $impuesto = True;
        } else {
            $impuesto = False;
        }

        $producto = new Producto();
        $producto->setCodigo($data->get('codigo'));
        $producto->setDetalle($data->get('detalle'));
        $producto->setPrecioUnit($data->get('precio'));
        $producto->setStock($data->get('stock'));
        $producto->setImpuesto($impuesto);
        $em->persist($producto);
        $em->flush();

        $response->setContent(json_encode([
            'id' => $producto->getId(),
            'codigo' => $producto->getCodigo(),
            'detalle' => $producto->getDetalle(),
            'precio' => $producto->getPrecioUnit(),
            'stock' => $producto->getStock(),
            'impuesto' => $producto->getImpuesto(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/productos/eliminarProducto", name="eliminarProducto")
     */
    public function eliminarProducto()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Producto::class);

        $data = $request->request;
        $id = $data->get('id');
        $producto = $repository->find($id);

        $em->remove($producto);
        $em->flush();

        $response->setContent(json_encode([
            'eliminado' => 'ok'
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/productos/editarProducto", name="editarProducto")
     */
    public function editarProducto()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;

        if($data->get('impuesto') == "true"){
            $impuesto = True;
        } else {
            $impuesto = False;
        }

        $producto = $em->getRepository(Producto::class)->find($data->get('id'));
        $msj = "";

        if (!$producto) {
            $msj = 'err';
        } else {
            $producto->setCodigo($data->get('codigo'));
            $producto->setDetalle($data->get('detalle'));
            $producto->setPrecioUnit($data->get('precio'));
            $producto->setStock($data->get('stock'));
            $producto->setImpuesto($impuesto);
            $em->flush();
            $msj = 'Creado con exito';
        }

        $response->setContent(json_encode([
            'id' => $producto->getId(),
            'codigo' => $producto->getCodigo(),
            'detalle' => $producto->getDetalle(),
            'precio' => $producto->getPrecioUnit(),
            'stock' => $producto->getStock(),
            'impuesto' => $producto->getImpuesto(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/ajaxtest", name="ajaxtest")
     */
    public function ajaxtest()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();

        $data = $request->request;

        $producto = new Producto();
        $producto->setCodigo($data->get('codigo'));
        $producto->setDetalle($data->get('detalle'));
        $producto->setPrecioUnit($data->get('precio'));
        $producto->setStock($data->get('stock'));
        $producto->setImpuesto($data->get('impuesto'));
        $em->persist($producto);
        $em->flush();

        $response->setContent(json_encode([
            'data' => $data,
        ]));
        $response->headers->set('Content-Type', 'application/json');

        

        return $response;
        

    }
}
