$(function() {
  /*Permer de grise un document (moule et moule - presse) et le le mettre en mode supression*/
    $('.toggle-event').change(function() {
      if($(this).prop('checked')){
        $('.'+$(this).attr('data-link')).removeAttr('readonly');
        $('.'+$(this).attr('data-link')).css('background','');
      }
      else{
        $('.'+$(this).attr('data-link')).attr('readonly','readonly');
        $('.'+$(this).attr('data-link')).css('background','gainsboro');
      }
    });

    var inputChange = function(){
      var val=parseInt($(this).attr('id'))+1;
      if($(this).val()!=''){
        if($("#"+val).length==0){
          var c = $(this).clone();
          c.attr('id',val);
          c.removeClass('inputClass');
          c.addClass('newElement');
          c.val('');
          c.removeAttr('value');
          c.on('keyup change',inputChange)
          c.appendTo("#inputgroup" );
        }
      }
      else {
          $($("#"+val)).remove();
      }
    }

    var addChangeDoc = function(){
      var val_old=parseInt($(this).attr('id'));
      var val=val_old+1;
      if($(this).val()!=''){
        var input = $('input[name="doc[new]['+val+'][lien]"]');
          if($('input[name="doc[new]['+val+'][lien]"]').length==0){

            var c = $(this).parent().parent().clone();


            console.log($('input[name="doc[new]['+val+'][lien]"]').length==0);

            c.children().children()[0].name="doc[new]["+val+"][id_doc_type]";
            c.children().children()[1].name="doc[new]["+val+"][lien]";
            c.children().children()[1].value="";
            c.children().children()[1].id=val;

            c.appendTo("#documentElement > tbody" );

            $('input[name="doc[new]['+val+'][lien]"]').on('keyup change',addChangeDoc);
          }
      }
      else {
          $(this).parent().parent().remove();
      }
    }

    var addChangeDocMP = function addChangeDocMP(){
      var val_old=parseInt($(this).attr('id'));
      var val=val_old+1;

      if($(this).val()!=''){
        var tr = $(this).parent().parent();
        var id = tr.parent().parent()[0].id;
        var idMP = id.substring(5);//docMP 5

          if($('input[name="docMP[new]['+idMP+']['+val+'][lien]"]').length==0){


            var c = tr.clone();



            c.children().children()[0].name="docMP[new]["+idMP+"]["+val+"][id_doc_type]";
            c.children().children()[1].name="docMP[new]["+idMP+"]["+val+"][lien]";
            c.children().children()[1].value="";
            c.children().children()[1].id=val;

            c.appendTo("#"+id+"  > tbody" );

            $('input[name="docMP[new]['+idMP+']['+val+'][lien]"]').on('keyup change',addChangeDocMP);
          }
      }
      else {
          $(this).parent().parent().remove();
      }
    }


/* nom presse et libelé de document typeDoc*/
    $(".itemFormListe > input").on('keyup change',function(){$(this).attr('changed','changed')});
/* document moule*/
  //$(".formDoc > input").on('keyup change',function(){$(this).attr('changed','changed')});

/* nom presse et libelé de document typeDoc*/
   $("#inputgroup > input").on('keyup change',inputChange);
/* document moule*/
  $("#documentElement > tbody > tr.formNewDoc > td > input").on('keyup change',addChangeDoc);

  /* document MP*/
  $(".documentMPElement > tbody > tr.formNewDocMP > td > input").on('keyup change',addChangeDocMP);

  });
