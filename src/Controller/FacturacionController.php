<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Producto;
use App\Entity\Cliente;
use App\Entity\Empresa;
use App\Entity\Configuracion;
use Doctrine\ORM\EntityManagerInterface;



class FacturacionController extends AbstractController
{
    /**
     * @Route("/facturacion", name="facturacion")
     */
    public function index()
    {

        $em = $this->getDoctrine()->getManager();
        $ConfRepo = $em->getRepository(Configuracion::class);
        $ClienteRepo = $em->getRepository(Cliente::class);
        $ProductoRepo = $em->getRepository(Producto::class);
        $EmpresaRepo = $em->getRepository(Empresa::class);

        $clientes = $ClienteRepo->findAll();
        $productos = $ProductoRepo->findAll();
        $empresas = $EmpresaRepo->findAll();
        $configuracion = $ConfRepo->findAll();

        if(count($configuracion) > 0){
            $registro = $configuracion[0];
            $establecimiento = $registro->getEstablecimiento();
            $puntoEmision = $registro->getPuntoEmision();
            $secuencia = $registro->getSecFactura();
        } else {
            $establecimiento = "000";
            $puntoEmision = "000";
            $secuencia = "000";
        }


        return $this->render('facturacion/index.html.twig', [
            'controller_name' => 'FacturacionController',
            'clientes' => $clientes,
            'establecimiento' => $establecimiento,
            'puntoEmision' => $puntoEmision,
            'secuencia' => $secuencia,
            'empresas' => $empresas,
            'productos' => $productos

        ]);
    }


    /**
     * @Route("/facturacion/facturar", name="facturar")
     */
    public function facturar()
    {

        $em = $this->getDoctrine()->getManager();
        $ConfRepo = $em->getRepository(Configuracion::class);
        $ClienteRepo = $em->getRepository(Cliente::class);
        $ProductoRepo = $em->getRepository(Producto::class);
        $EmpresaRepo = $em->getRepository(Empresa::class);

        $clientes = $ClienteRepo->findAll();
        $productos = $ProductoRepo->findAll();
        $empresas = $EmpresaRepo->findAll();
        $configuracion = $ConfRepo->findAll();

        if(count($configuracion) > 0){
            $registro = $configuracion[0];
            $establecimiento = $registro->getEstablecimiento();
            $puntoEmision = $registro->getPuntoEmision();
            $secuencia = $registro->getSecFactura();
        } else {
            $establecimiento = "000";
            $puntoEmision = "000";
            $secuencia = "000";
        }


        return $this->render('facturacion/factura.html.twig', [
            'controller_name' => 'FacturacionController',
            'clientes' => $clientes,
            'establecimiento' => $establecimiento,
            'puntoEmision' => $puntoEmision,
            'secuencia' => $secuencia,
            'empresas' => $empresas,
            'productos' => $productos

        ]);
    }


    /**
     * @Route("/facturacion/precioStock", name="precio")
     */
    public function actualizarPrecioStock()
    {


        $response = new Response();
        $request = Request::createFromGlobals();
        $data = $request->request;

        $producto_id = $data->get('id');
        $cantidad_solicitada = $data->get('cantidad');
        $precio_actualizado = $data->get('nuevoPrecio');


        $em = $this->getDoctrine()->getManager();
        $ProductoRepo = $em->getRepository(Producto::class);
        $producto = $ProductoRepo->find($producto_id);
        $stock_anterior = $producto->getStock();
        $stock_actual = $stock_anterior - $cantidad_solicitada;
        $producto->setPrecioUnit($precio_actualizado);
        $producto->setStock($stock_actual);
        $em->flush();

        $response->setContent(json_encode([
            'ok' => 'ko',
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

       
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
        $repository = $this->getDoctrine()->getRepository(Empresa::class);
        $empresas = $repository->findAll();

        return $this->render('facturacion/empresas.html.twig', [
            'controller_name' => 'FacturacionController',
            'empresas' => $empresas
        ]);
    }


    /**
     * @Route("/empresas/nuevaEmpresa", name="nuevoEmpresa")
     */
    public function nuevaEmpresa()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;


        $empresa = new Empresa();

        $empresa->setRuc($data->get('ruc'));
        $empresa->setRazonSocial($data->get('razonsocial'));
        $empresa->setDireccion($data->get('direccion'));
        $em->persist($empresa);
        $em->flush();

        $response->setContent(json_encode([
            'id' => $empresa->getId(),
            'ruc' => $empresa->getRuc(),
            'razonsocial' => $empresa->getRazonSocial(),
            'direccion' => $empresa->getDireccion(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/empresas/verificarRUC", name="verificarRUCEmpresa")
     */
    public function verificarEmpresa()
    {
        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Empresa::class);
        $data = $request->request;
        $ruc = $data->get('ruc');
        $empresa = $repositorio->findOneBy(['ruc' => $ruc]);

        if (!$empresa) {
            $msj = 'disponible';
        } else {
            if($data->get('id') == $empresa->getId()){
                $msj = 'disponible';
            } else {
                $msj = 'ocupado';    
            }
        }

        $response->setContent(json_encode(['msj' => $msj]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/empresas/eliminarEmpresa", name="eliminarEmpresa")
     */
    public function eliminarEmpresa()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Empresa::class);

        $data = $request->request;
        $id = $data->get('id');
        $empresa = $repositorio->find($id);

        $em->remove($empresa);
        $em->flush();

        $response->setContent(json_encode([
            'eliminado' => 'ok'
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/empresas/editarEmpresa", name="editarEmpresa")
     */
    public function editarEmpresa()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;

        $empresa = $em->getRepository(Empresa::class)->find($data->get('id'));
        $msj = "";

        if (!$empresa) {
            $msj = 'err';
        } else {
            $empresa->setRuc($data->get('ruc'));
            $empresa->setRazonSocial($data->get('razonsocial'));
            $empresa->setDireccion($data->get('direccion'));
            $em->flush();
            $msj = 'Creado con exito';
        }

        $response->setContent(json_encode([
            'id' => $empresa->getId(),
            'ruc' => $empresa->getRuc(),
            'razonsocial' => $empresa->getRazonSocial(),
            'direccion' => $empresa->getDireccion(),
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Configuracion::class);
        $configuracion = $repositorio->findAll();
        if(count($configuracion) > 0){
            $registro = $configuracion[0];
            $establecimiento = $registro->getEstablecimiento();
            $puntoEmision = $registro->getPuntoEmision();
            $secuencia = $registro->getSecFactura();
        } else {
            $establecimiento = 0;
            $puntoEmision = 0;
            $secuencia = 0;
        }

        return $this->render('facturacion/configuracion.html.twig', [
            'controller_name' => 'FacturacionController',
            'establecimiento' => $establecimiento,
            'puntoEmision' => $puntoEmision,
            'secuencia' => $secuencia,
            
        ]);
    }


    /**
     * @Route("/configuracion/actualizar", name="actualizarConfig")
     */
    public function actualizarConfiguracion()
    {
        
        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Configuracion::class);
        $data = $request->request;

        $id = $data->get('id');
        $configuracion = $repositorio->findAll();
        if(count($configuracion) > 0){
            $registro = $configuracion[0];
            $registro->setEstablecimiento($data->get('establecimiento'));
            $registro->setPuntoEmision($data->get('puntoEmision'));
            $registro->setSecFactura($data->get('secuencia'));
            $em->persist($registro);
            $em->flush();
        } else {
            $registro = new Configuracion();
            $registro->setEstablecimiento($data->get('establecimiento'));
            $registro->setPuntoEmision($data->get('puntoEmision'));
            $registro->setSecFactura($data->get('secuencia'));
            $em->persist($registro);
            $em->flush();
        }

        $response->setContent(json_encode([
            'eliminado' => $registro
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
