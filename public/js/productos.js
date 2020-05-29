function abrirModalForm(accion, id){

    $("#ModalForm").modal('show');
    $("#Modaltitulo").empty();
    if(accion == "nuevo"){
      $("#Modaltitulo").append("Nuevo Producto");
      $("#codigo").val("");
      $("#precio").val("");
      $("#detalle").val("");
      $("#stock").val("");
      $("#btn-modal-form").attr("onclick", "nuevoProducto()");


    } else {
      $("#Modaltitulo").append("Editar Producto N#" + id);
      row = $(".id-producto[value='"+id+"']").parent().parent().find("td");
      
      precio = row[3].outerText;
      precio = parseInt(precio.substring(1));
      $("#codigo").val(row[1].outerText);
      $("#precio").val(precio);
      $("#detalle").val(row[2].outerText);
      $("#stock").val(row[4].outerText);
      $("#btn-modal-form").attr("onclick", "editarProducto('"+id+"')");



    }
    
  }


  function nuevoProducto(){

    $(".error").hide();

    codigo = $("#codigo").val();
    precio = $("#precio").val();
    detalle = $("#detalle").val();
    stock = $("#stock").val();

    if( codigo =="" || precio =="" || detalle =="" || stock =="" ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" Por favor ingresar todos los datos");

    } else if( precio <= 0 ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" El precio no puede ser cero o menor");

    } else if( stock <= 0 ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" El stock no puede ser cero o menor");

    } else {

      $.ajax({
        type:"POST",
        url:"/productos/nuevoProducto",
        data:{
          'codigo': codigo,
          'precio': precio,
          'detalle': detalle,
          'stock': stock,

        },
        success: function(datos){

          $("#codigo").val("");
          $("#precio").val("");
          $("#detalle").val("");
          $("#stock").val("");
          
          $("#ModalForm").modal('hide');

          idSpan = '<span class="id-producto" value="'+datos["id"]+'">'+datos["id"]+'</span>';

          acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
          '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

          var table = $('#tabla-productos').DataTable();
          table.row.add([
              idSpan,
              datos["codigo"],
              datos["detalle"],
              "$"+datos["precio"],
              datos["stock"],
              acciones
            ]).draw();
        }
      });
    }
  }


  function alertaEliminar(id){
    $("#Advertencia").modal("show");
    $(".eliminar-definitivo").attr("value", id);
  }


  function eliminarProducto(id){

     $.ajax({
        type:"POST",
        url:"/productos/eliminarProducto",
        data:{
          'id': id
        },
        success: function(datos){
          row = $(".id-producto[value='"+id+"']").parent().parent();
          table = $('#tabla-productos').DataTable();
          table.row( row ).remove().draw();
        }
      });
  }


  function editarProducto(id){
    $(".error").hide();

    codigo = $("#codigo").val();
    precio = $("#precio").val();
    detalle = $("#detalle").val();
    stock = $("#stock").val();

    if( codigo =="" || precio =="" || detalle =="" || stock =="" ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" Por favor ingresar todos los datos");

    } else if( precio <= 0 ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" El precio no puede ser cero o menor");

    } else {

      $.ajax({
        type:"POST",
        url:"/productos/editarProducto",
        data:{
          'id':id,
          'codigo': codigo,
          'precio': precio,
          'detalle': detalle,
          'stock': stock,

        },
        success: function(datos){

          $("#codigo").val("");
          $("#precio").val("");
          $("#detalle").val("");
          $("#stock").val("");
          
          $("#ModalForm").modal('hide');
          idSpan = '<span class="id-producto" value="'+datos["id"]+'">'+datos["id"]+'</span>';

          acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
          '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

          row = $(".id-producto[value='"+id+"']").parent().parent();

          var table = $('#tabla-productos').DataTable();
          newData = [ idSpan,
                      datos["codigo"],
                      datos["detalle"],
                      "$"+datos["precio"],
                      datos["stock"],
                      acciones ];
          table.row(row).data( newData ).draw();


        }
      });
    }
  }