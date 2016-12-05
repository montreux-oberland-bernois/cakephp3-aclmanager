/**
 * Created by rgabriel on 18.03.2016.
 */



$(document).ready(function() {
    $('[data-toggle="popover"]').popover();


} ).on("click", '.click-acl-inherit, .click-acl-allow, .click-acl-deny' ,function(){
    updateAcl($(this))
}).on('click', 'i[data-trigger="contract"]', function () {
    $('tr[data-parent="'+$(this).data('id')+'"]').hide();
    $(this).attr('data-trigger', "expand").removeClass().addClass("fa fa-plus-square click");
}).on('click', 'i[data-trigger="expand"]', function () {
    $('tr[data-parent="'+$(this).data('id')+'"]').show();
    $(this).attr('data-trigger', "contract").removeClass().addClass("fa fa-minus-square click");
} );

function updateAcl($this){
    var statusName, status, icon, aco, aro;

    if($this.hasClass("click-acl-deny")){
        //deny
        statusName = "deny";
        icon = "check text-success";
    }else if($this.hasClass("click-acl-allow")){
        //allow
        statusName = "allow";
        icon = "remove text-danger";
    }else if($this.hasClass("click-acl-inherit")){
        //inherit
        statusName = "inherit";
        icon = "level-down text-primary";
    }

    var  data = {
        aro: $this.data('aro'),
        aco: $this.data('aco'),
        model: $this.data('model'),
        status: statusName
    };

    $this.children().removeClass().addClass("fa fa-refresh fa-spin text-default");

    $.ajax({
        type: "POST",
        url: baseUrl +'AclManager/acl/AjaxUpdatePermissions',
        data: data
    })
        .done(function() {
            $this.removeClass().addClass("click-acl-"+statusName);
            $this .children().removeClass().addClass("fa fa-"+icon);
            dataA = dataD = undefined;
        })
        .fail(function() {
            alert( "Oops" );
        });
}