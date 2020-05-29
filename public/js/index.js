function filtrar(){
        var table = $('#facturas').DataTable();
        factura = $('#nfactura').val();
        cliente = $('#filtro-clientes').val();
        fechainicio = $('#fechainicio').val();
        fechafinal = $('#fechafinal').val();

        if(factura != ''){
            table.columns(1).search(factura).draw();
        }
        if(cliente != 'Todos'){
            table.columns(3).search(cliente).draw();
        }
        if(fechainicio != ''){
            table.draw();
        }
        if(fechafinal != ''){
            table.draw();
        }
    }

    
    function alertaEliminar(id){
        $("#Advertencia").modal("show");
        $(".eliminar-definitivo").attr("value", id);
      }

    function eliminarFactura(id){

         $.ajax({
            type:"POST",
            url:"/facturacion/eliminarFactura",
            data:{
              'id': id
            },
            success: function(datos){
              row = $(".id-factura[value='"+id+"']").parent();
              table = $('#facturas').DataTable();
              table.row( row ).remove().draw();
            }
          });
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
            var table = $('#facturas').DataTable();
            table.columns().search("").draw();
        }
    }