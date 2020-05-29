

function abrirModalForm(accion, id)
{
    $("#ModalForm").modal('show');
    $("#Modaltitulo").empty();

    if(accion == "nuevo"){
        $("#Modaltitulo").append("Nuevo Cliente");
        $("#ruc").val("");
        $("#telefono").val("");
        $("#razonSocial").val("");
        $("#direccion").val("");
        $("#btn-modal-form").attr("onclick", "nuevoCliente()");
    } else {
        $("#Modaltitulo").append("Editar Cliente N#" + id);
        row = $(".id-cliente[value='"+id+"']").parent().parent().find("td");
        $("#ruc").val(row[1].outerText);
        $("#telefono").val(row[3].outerText);
        $("#razonSocial").val(row[2].outerText);
        $("#direccion").val(row[4].outerText);

        $("#btn-modal-form").attr("onclick", "editarCliente('"+id+"')");
    } 
}


function nuevoCliente()
{

    $(".error").hide();
    ruc = $("#ruc").val();
    telefono = $("#telefono").val();
    razonSocial = $("#razonSocial").val();
    direccion = $("#direccion").val();

    if( ruc =="" || telefono =="" || razonSocial =="" || direccion =="" ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" Por favor ingresar todos los datos");

    } else if( ruc.match(/^[0-9]+$/) == null ){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" Solo ingrese numeros en el RUC/CI");

    } else if( telefono.match(/^[0-9]+$/) == null ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Solo ingrese numeros en el telefono");

    } else {

        $.ajax({
            type:"POST",
            url:"/clientes/verificarRUC",
            data:{
              'ruc': ruc,
              'id': 0
            },
            success: function(datos){
                if(datos["msj"] == "ocupado"){
                  $(".error").show();
                  $("#msje").empty();
                  $("#msje").append(" El RUC ingresado ya esta registrado.");

                } else {

                   $.ajax({
                    type:"POST",
                    url:"/clientes/nuevoCliente",
                    data:{
                      'ruc': ruc,
                      'razonsocial': razonSocial,
                      'telefono': telefono,
                      'direccion': direccion,

                    },
                    success: function(datos){

                      $("#ruc").val("");
                      $("#telefono").val("");
                      $("#razonSocial").val("");
                      $("#direccion").val("");
                      
                      $("#ModalForm").modal('hide');

                      idSpan = '<span class="id-cliente" value="'+datos["id"]+'">'+datos["id"]+'</span>';

                      acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
                      '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

                      var table = $('#tabla-clientes').DataTable();
                      table.row.add([
                          idSpan,
                          datos["ruc"],
                          datos["razonsocial"],
                          datos["telefono"],
                          datos["direccion"],
                          acciones
                        ]).draw();
                    }
                    
                  });

                }
            }
        });



     
    }
}


function alertaEliminar(id)
{
      $("#Advertencia").modal("show");
      $(".eliminar-definitivo").attr("value", id);
}


function eliminarCliente(id)
{

   $.ajax({
      type:"POST",
      url:"/clientes/eliminarCliente",
      data:{
        'id': id
      },
      success: function(datos){
        row = $(".id-cliente[value='"+id+"']").parent().parent();
        table = $('#tabla-clientes').DataTable();
        table.row( row ).remove().draw();
      }
    });
}


function editarCliente(id)
{
      $(".error").hide();

      ruc = $("#ruc").val();
      telefono = $("#telefono").val();
      razonSocial = $("#razonSocial").val();
      direccion = $("#direccion").val();

       if( ruc =="" || telefono =="" || razonSocial =="" || direccion =="" ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Por favor ingresar todos los datos");

      } else if( ruc.match(/^[0-9]+$/) == null ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Solo ingrese numeros en el RUC/CI");

      } else if( telefono.match(/^[0-9]+$/) == null ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Solo ingrese numeros en el telefono");

      } else {


        $.ajax({
              type:"POST",
              url:"/clientes/verificarRUC",
              data:{
                'ruc': ruc,
                'id': id
              },
              success: function(datos){
                  if(datos["msj"] == "ocupado"){
                    $(".error").show();
                    $("#msje").empty();
                    $("#msje").append(" El RUC ingresado ya esta registrado.");
                  } else {
                    $.ajax({
                      type:"POST",
                      url:"/clientes/editarCliente",
                      data:{
                        'id':id,
                        'ruc': ruc,
                        'telefono': telefono,
                        'razonsocial': razonSocial,
                        'direccion': direccion,

                      },
                      success: function(datos){

                        $("#ruc").val("");
                        $("#telefono").val("");
                        $("#razonSocial").val("");
                        $("#direccion").val("");
                        
                        $("#ModalForm").modal('hide');
                        idSpan = '<span class="id-cliente" value="'+datos["id"]+'">'+datos["id"]+'</span>';

                        acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
                        '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

                        row = $(".id-cliente[value='"+id+"']").parent().parent();

                        var table = $('#tabla-clientes').DataTable();
                        newData = [ idSpan,
                                    datos["ruc"],
                                    datos["razonsocial"],
                                    datos["telefono"],
                                    datos["direccion"],
                                    acciones ];
                        table.row(row).data( newData ).draw();


                      }
                    });

                  }
                }
              });




        
      }
}