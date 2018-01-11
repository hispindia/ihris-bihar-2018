var cancelling_transfer = new Array();

function cancelStaffTransfer(node,transfer_log_id,person_id) {
    node = $(node);
    if (!node) {
	return;
    }
    /*var color = 'white';
    var score = node.get('value');
    if (score >= passing_score) {
	color = 'green';
    } else {
	color = 'red';
    }*/
    if ( !cancelling_transfer[transfer_log_id] ) {
        cancelling_transfer[transfer_log_id] = true;
        var url = 'index.php/canceltransfer';
        var req = new Request.HTML({
            method: 'get',
            url: url,
            data: { 'transfer_log_id':transfer_log_id, 'parent' : person_id},
            
            onRequest: function() { node.set('text', 'Cancelling transfer...'); },
            update: node,
            onComplete: function(response) { 
              cancelling_transfer[transfer_log_id] = false; 
              node.set('text','Transfer Canceled'); 
            }
            /*
            onRequest: function() { node.set('value', 'Updating score...'); node.setStyle('color','black');},
            onComplete: function(response) { editstudent_inprogress[exam] = false;  node.set('value',score); node.setStyle('color',color);}
            */
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}
