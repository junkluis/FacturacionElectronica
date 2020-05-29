function abrirModalForm(accion, id){

    $("#ModalForm").modal('show');
    $("#Modaltitulo").empty();
    if(accion == "nuevo"){
      $("#Modaltitulo").append("Nueva Empresa");
      $("#razonSocial").val("");
      $("#ruc").val("");
      $("#direccion").val("");
      $("#btn-modal-form").attr("onclick", "nuevaEmpresa()");


    } else {
      $("#Modaltitulo").append("Editar Empresa N#" + id);
      row = $(".id-empresa[value='"+id+"']").parent().parent().find("td");
      $("#ruc").val(row[1].outerText);
      $("#razonSocial").val(row[2].outerText);
      $("#direccion").val(row[3].outerText);

      $("#btn-modal-form").attr("onclick", "editarEmpresa('"+id+"')");
      
    }
    
  }


  function nuevaEmpresa(){

    $(".error").hide();

    razonSocial = $("#razonSocial").val();
    ruc = $("#ruc").val();
    direccion = $("#direccion").val();

    if( razonSocial =="" || ruc =="" || direccion =="" ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Por favor ingresar todos los datos");

    } else if( ruc.match(/^[0-9]+$/) == null ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Solo ingrese numeros en el RUC");

    }  else {

      $.ajax({
        type:"POST",
        url:"/empresas/verificarRUC",
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
                        url:"/empresas/nuevaEmpresa",
                        data:{
                          'ruc': ruc,
                          'razonsocial': razonSocial,
                          'direccion': direccion,

                        },
                        success: function(datos){

                          $("#razonSocial").val("");
                          $("#ruc").val("");
                          $("#direccion").val("");
                          $("#ModalForm").modal('hide');

                          idSpan = '<span class="id-empresa" value="'+datos["id"]+'">'+datos["id"]+'</span>';

                          acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
                          '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

                          var table = $('#tabla-empresas').DataTable();
                          table.row.add([
                              idSpan,
                              datos["ruc"],
                              datos["razonsocial"],
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


  function alertaEliminar(id){
    $("#Advertencia").modal("show");
    $(".eliminar-definitivo").attr("value", id);
  }


  function eliminarEmpresa(id){

     $.ajax({
        type:"POST",
        url:"/empresas/eliminarEmpresa",
        data:{
          'id': id
        },
        success: function(datos){
          row = $(".id-empresa[value='"+id+"']").parent().parent();
          table = $('#tabla-empresas').DataTable();
          table.row( row ).remove().draw();
        }
      });
  }


  function editarEmpresa(id){
    $(".error").hide();

    razonSocial = $("#razonSocial").val();
    ruc = $("#ruc").val();
    direccion = $("#direccion").val();

    if( razonSocial =="" || ruc =="" || direccion ==""){
      $(".error").show();
      $("#msje").empty();
      $("#msje").append(" Por favor ingresar todos los datos");

    } else if( ruc.match(/^[0-9]+$/) == null ){
        $(".error").show();
        $("#msje").empty();
        $("#msje").append(" Solo ingrese numeros en el RUC");

    } else {

      $.ajax({
        type:"POST",
                url:"/empresas/verificarRUC",
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
                        url:"/empresas/editarEmpresa",
                        data:{
                          'id':id,
                          'ruc': ruc,
                          'razonsocial': razonSocial,
                          'direccion': direccion,

                        },
                        success: function(datos){

                          $("#ruc").val("");
                          $("#razonSocial").val("");
                          $("#direccion").val("");
                          
                          $("#ModalForm").modal('hide');
                          idSpan = '<span class="id-empresa" value="'+datos["id"]+'">'+datos["id"]+'</span>';

                          acciones = '<button type="button" class="btn btn-secondary btn-editar" value="'+datos["id"]+
                          '"><i class="fas fa-pen"></i> </button> <button type="button" class="btn btn-danger btn-eliminar" value="'+datos["id"]+'"> <i class="fas fa-times"></i></button>';

                          row = $(".id-empresa[value='"+id+"']").parent().parent();

                          var table = $('#tabla-empresas').DataTable();
                          newData = [ idSpan,
                                      datos["ruc"],
                                      datos["razonsocial"],
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
