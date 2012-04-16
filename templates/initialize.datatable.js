$(document).ready(function() {
				/* Init DataTables */
				var oTable = $('#example').dataTable();
				 
				/* Apply the jEditable handlers to the table */
				$('td', oTable.fnGetNodes()).editable( '/int_res/editable_ajax.php', {
						"callback": function( sValue, y ) {
								var aPos = oTable.fnGetPosition( this );
								oTable.fnUpdate( sValue, aPos[0], aPos[1] );
						},
						"submitdata": function ( value, settings ) {
								return {
										"row_id": this.parentNode.getAttribute('id'),
										"column": oTable.fnGetPosition( this )[2]
								};
						},
						"height": "14px"
				} );
		} );