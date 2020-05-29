function limpiarCampos(){
        establecimiento = $("#establecimiento").val("");
        puntoEmision = $("#puntoEmision").val("");
        secuencia = $("#secuencia").val("");
    }

    function actualizarConfiguracion(){

        establecimiento = $("#establecimiento").val();
        puntoEmision = $("#puntoEmision").val();
        secuencia = $("#secuencia").val();

        if( establecimiento =="" || puntoEmision =="" || secuencia =="" ){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" Se requiere que llene todos los campos");

        }  else if( establecimiento.match(/^[0-9]{3}$/) == null ){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" El establecimiento debe tener 3 numeros enteros");

        } else if( puntoEmision.match(/^[0-9]{3}$/) == null ){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" El Punto de emision debe tener 3 numeros enteros");
        } else if( secuencia.match(/^[0-9]{9}$/) == null ){
            $(".error").show();
            $("#msje").empty();
            $("#msje").append(" La Secuencia debe tener 9 numeros enteros");
        } else {

            $.ajax({
                type:"POST",
                url:"/configuracion/actualizar",
                data:{
                    'establecimiento': establecimiento,
                    'puntoEmision': puntoEmision,
                    'secuencia': secuencia,
                },
                success: function(datos){
                    $(".error").hide();
                    $(".listo").show();
                    $("#mensaje").empty();
                    $("#mensaje").append(" Se actualizaron los campos");
                }
            });
        }
    }