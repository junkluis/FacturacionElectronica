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
use App\Entity\Factura;
use App\Entity\DetalleFactura;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;




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
        $Facturasepo = $em->getRepository(Factura::class);

        $facturas = $Facturasepo->findAll();

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
            'productos' => $productos,
            'facturas' => $facturas

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
     * @Route("/facturacion/registrarFactura", name="registrarFactura")
     */
    public function registrarFactura()
    {
        $response = new Response();
        $request = Request::createFromGlobals();
        $data = $request->request;
        $em = $this->getDoctrine()->getManager();
        $PorductoRepo = $em->getRepository(Producto::class);

        $carrito_compras = $data->get('carrito_compras');

        foreach ($carrito_compras as $compra) {
            $id = intval($compra['productoId']);
            $producto = $PorductoRepo->find($id);
            $producto->setPrecioUnit(floatval($compra['precio']));
            $nuevo_stock = $producto->getStock() - intval($compra['cantidad']);
            $producto->setStock($nuevo_stock);
            $em->flush();
        }

        //Buscar empresa y cliente
        $empresa_id = intval($data->get('empresa'));
        $cliente_id = intval($data->get('cliente'));
        $empresa = $em->getRepository(Empresa::class)->find($empresa_id);
        $cliente = $em->getRepository(Cliente::class)->find($cliente_id);

        $fecha = $data->get('fecha');
        $fecha_emision = new \DateTime($fecha[2].'-'.$fecha[0].'-'.$fecha[1]);

        //Crear nueva factura
        $nueva_factura = new Factura();
        $nueva_factura->setEstablecimiento($data->get('establecimiento'));
        $nueva_factura->setPuntoEmision($data->get('ptoEmision'));
        $nueva_factura->setSecFactura($data->get('secFactura'));
        $nueva_factura->setEmpresa($empresa);
        $nueva_factura->setCliente($cliente);
        $nueva_factura->setFecha($fecha_emision);
        $nueva_factura->setSubtotal($data->get('subtotal'));
        $nueva_factura->setImpuestos($data->get('impuestos'));
        $nueva_factura->setTotal($data->get('total'));

        $em->persist($nueva_factura);
        $em->flush();

        //aumentar la secuencia
        $config = $em->getRepository(Configuracion::class)->findAll();
        $registro = $config[0];
        $secuencia_actual = intval($registro->getSecFactura());
        $nueva_secuencia = strval($secuencia_actual+1);
        $len_faltante = 9 - strlen($nueva_secuencia);
        if($len_faltante > 0){
            for ($i = 1; $i <= $len_faltante; $i++) {
                $nueva_secuencia = '0'.$nueva_secuencia;
            }
        }
        $registro->setSecFactura($nueva_secuencia);
        $em->flush();


        //Registrar los detalles
        foreach ($carrito_compras as $compra) {
            $id = intval($compra['productoId']);
            $producto = $PorductoRepo->find($id);
            $detalle = new DetalleFactura();
            $detalle->setFactura($nueva_factura);
            $detalle->setProducto($producto);
            $detalle->setCantidad(intval($compra['cantidad']));
            $em->persist($detalle);
            $em->flush();
        }
        
        $response->setContent(json_encode(['factura_id' => $nueva_factura->getId()]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/facturacion/cod{facturaid}", name="revisarFactura", requirements={"facturaid"="\d+"})
     */
    public function revisarFactura(int $facturaid)
    {


        $em = $this->getDoctrine()->getManager();
        $factura = $em->getRepository(Factura::class)->find($facturaid);
        $detalles = $factura->getDetalleFacturas();

        return $this->render('facturacion/revisarFactura.html.twig', [
            'controller_name' => 'FacturacionController',
            'factura' => $factura,
            'detalles'=> $detalles
        ]);
    }

     /**
     * @Route("/facturacion/cod{facturaid}/imprimir", name="imprimir", requirements={"facturaid"="\d+"})
     */
    public function imprimir(int $facturaid)
    {


        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $factura = $em->getRepository(Factura::class)->find($facturaid);
        $detalles = $factura->getDetalleFacturas();


        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('facturacion/pdfTemplate.html.twig', [
            'factura' => $factura,
            'detalles' => $detalles,
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("Factura.pdf", [
            "Attachment" => true
        ]);


        return $this->redirectToRoute('revisarFactura', ['facturaid' => $factura->getId()]);

    }



    /**
     * @Route("/facturacion/eliminarFactura", name="eliminarFactura")
     */
    public function eliminarFactura()
    {

        $response = new Response();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository(Factura::class);

        $data = $request->request;
        $id = $data->get('id');
        $factura = $repositorio->find($id);

        $em->remove($factura);
        $em->flush();

        $response->setContent(json_encode([
            'eliminado' => 'ok'
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
        $producto = new Producto();
        $producto->setCodigo($data->get('codigo'));
        $producto->setDetalle($data->get('detalle'));
        $producto->setPrecioUnit($data->get('precio'));
        $producto->setStock($data->get('stock'));
        $producto->setImpuesto(True);
        $em->persist($producto);
        $em->flush();

        $response->setContent(json_encode([
            'id' => $producto->getId(),
            'codigo' => $producto->getCodigo(),
            'detalle' => $producto->getDetalle(),
            'precio' => $producto->getPrecioUnit(),
            'stock' => $producto->getStock(),
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

        $producto = $em->getRepository(Producto::class)->find($data->get('id'));
        $msj = "";

        if (!$producto) {
            $msj = 'err';
        } else {
            $producto->setCodigo($data->get('codigo'));
            $producto->setDetalle($data->get('detalle'));
            $producto->setPrecioUnit($data->get('precio'));
            $producto->setStock($data->get('stock'));
            $producto->setImpuesto(true);
            $em->flush();
            $msj = 'Creado con exito';
        }

        $response->setContent(json_encode([
            'id' => $producto->getId(),
            'codigo' => $producto->getCodigo(),
            'detalle' => $producto->getDetalle(),
            'precio' => $producto->getPrecioUnit(),
            'stock' => $producto->getStock(),
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
        $em->persist($producto);
        $em->flush();

        $response->setContent(json_encode([
            'data' => $data,
        ]));
        $response->headers->set('Content-Type', 'application/json');

        

        return $response;
        

    }
}
