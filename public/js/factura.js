function actualizarSubtotal(){
        precio = $("#precioPro").val();
        cantidad = $("#cantidadPro").val();
        subtotal = parseFloat(precio) * parseFloat(cantidad);
        $("span.subtotal").empty();
        $("span.subtotal").append(subtotal.toFixed(2));
    }

    

    function mostrarFiltros(){
        if($("#filtros").is(":visible")){
            $("#filtros").slideUp();
            $("#btn-filtro").empty();
            $("#btn-filtro").append("Mostrar Filtros");
        } else {
            $("#filtros").slideDown();
            $("#btn-filtro").empty();
            $("#btn-filtro").append("Esconder Filtros");
        }
    }

    function agregarProducto(){
        $("#cantidadPro").val(1);
        $("#precioPro").val(0);
        $("span.stock").empty();
        $("span.subtotal").empty();
        $("span.stock").append("0");
        $("span.subtotal").append("00.00");
        $("#ModalProducto").modal('show');

    }


    function guardarProducto(){

        opcionSelect = $('#productos.selectpicker').find("option:selected");
        productoId = opcionSelect.attr("data-id");
        stock = opcionSelect.attr("data-stock");
        codigo = opcionSelect.attr("data-stock");
        detalle = opcionSelect.text();
        cantidad = $("#cantidadPro").val();
        precio = $("#precioPro").val();
        subtotal = parseFloat(precio) * parseFloat(cantidad);
        subtotal = subtotal.toFixed(2);
        

        if(parseInt(cantidad) > parseInt(stock)){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" No existen suficientes unidades");
        } else if(subtotal <= 0){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" Porfavor ingrese datos validos");
        } else if(opcionSelect.val() == -1){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" Seleccione un producto");
        } else {
            $(".error").hide();

            $("#detalles").append('<div class="form-row detalle registros" id="detalle-'+productoId+'" style="margin-top:5px">'+
                                           '<div class="form-group col-md-5">'+opcionSelect.text()+'</div>'+
                                           '<div class="form-group col-md-2 number">'+precio+'</div>'+
                                           '<div class="form-group col-md-2 number">'+cantidad+'</div>'+
                                           '<div class="form-group col-md-2 number subtotales">'+subtotal+'</div>'+
                                           '<div class="form-group col-md-1 number "><button type="button" data-id="'+productoId+'" class="btn btn-outline-danger eliminarDet"><i class="fas fa-minus-circle"></i></button></div>'+
                                         '</div>');

            $("#productos-options").append('<div id="producto-'+productoId+'" data-id="'+productoId+
                                           '" data-precio="'+precio+
                                           '" data-cantidad="'+cantidad+
                                           '" data-stock="'+stock+
                                           '" data-codigo="'+stock+
                                           '">'+detalle+'</div>');

            




            $("#ModalProducto").modal('hide');

            opcionSelect.remove();
            $('#productos.selectpicker').selectpicker('refresh');
            //opcionSelect.attr("value", precio);
            //opcionSelect.attr("data-stock", nuevoStock);
            //$('#productos.selectpicker').selectpicker('refresh');
        }

        actualizarTotales();

    }


    function eliminarDetalle(btn){
      productoId = $(btn).attr("data-id");
      productoInfo = $("#producto-"+productoId);
      detalleProducto = $("#detalle-"+productoId);
      precio = productoInfo.attr("data-precio");
      id = productoInfo.attr("data-id");
      stock = productoInfo.attr("data-stock");
      codigo = productoInfo.attr("data-codigo");
      detalle = productoInfo.text();

      $('#productos.selectpicker').append('<option value="'+precio+'" data-id="'+id+'" data-stock="'+stock+'" data-subtext="'+codigo+'">'+detalle+' </option>');
      $('#productos.selectpicker').selectpicker('refresh');

      productoInfo.remove();
      detalleProducto.remove();
      actualizarTotales();
    }


    function actualizarTotales(){
      subtotales = $(".subtotales");
      subtotal_principal = 0;
      impuesto = 0;
      total = 0;
      
      $.each(subtotales, function( index, value ) {
          subtotal_principal += parseFloat($(value).text());
      });
      
      impuesto = subtotal_principal * 0.12;
      total = subtotal_principal + impuesto;

      $('#valor-subtotal').empty();
      $('#valor-impuesto').empty();
      $('#valor-total').empty();
      $('#valor-subtotal').append("$"+parseFloat(subtotal_principal).toFixed(2));
      $('#valor-impuesto').append("$"+parseFloat(impuesto).toFixed(2));
      $('#valor-total').append("$"+parseFloat(total).toFixed(2));

    }

    function guardarFactura(){
      
      empresa = $('#empresa-emisora.selectpicker').val();
      cliente = $('#cliente-factura.selectpicker').val();

      //informacion de los producctos seleccionados
      productos = $("#productos-options").children();
      carrito = []      
      $.each(productos, function(index, value){
        detalle = {};
        detalle['productoId'] = $(value).attr("data-id");
        detalle['precio'] = $(value).attr("data-precio");
        detalle['cantidad'] = $(value).attr("data-cantidad");
        carrito.push(detalle);
      });

      //Datos de factura
      establecimiento = $('#establecimiento').text();
      ptoEmision = $('#ptoEmision').text();
      secuencia = $('#secuencia').text();
      fechaEmision = $('#fechaemision').val();
      fecha_campos = fechaEmision.split("/");
      subtotal = $('#valor-subtotal').text().substr(1);
      impuestos = $('#valor-impuesto').text().substr(1);
      total = $('#valor-total').text().substr(1);

      

      if(carrito.length == 0){
        $(".error-principal").show();
        $("#msje-error-principal").empty();
        $("#msje-error-principal").append(" Agrege un producto a la factura");

      } else if( fechaEmision == '' ){
        $(".error-principal").show();
        $("#msje-error-principal").empty();
        $("#msje-error-principal").append(" Seleccione una fecha de emision");
      } else {
        $.ajax({
            type:"POST",
            url:"/facturacion/registrarFactura",
            data:{
              'carrito_compras': carrito,
              'empresa': empresa,
              'cliente': cliente,
              'establecimiento': establecimiento,
              'ptoEmision': ptoEmision,
              'secFactura': secuencia,
              'fecha': fecha_campos,
              'subtotal': subtotal,
              'impuestos': impuestos,
              'total': total
            },
            success: function(datos){

              $(location).attr('href', '/facturacion/cod'+datos['factura_id']);
              
            }
        });
      }

    }