const awicon = '[data-fa-i2svg]';
var DropZoneLocation = 'default';
var temporiza;

/* jQuery Extended Function */
jQuery.fn.extend({
	
	verPass: function(elemento='div.input-group'){
		this.mouseenter(function(){
			$(this).parent().parent().find('input.iPass').attr('type','text');
			$(this).parent().parent().find('span').find(awicon).toggleClass('fa-eye fa-eye-slash');
		}).mouseleave(function(){
			$(this).parent().parent().find('input.iPass').attr('type','password');
			$(this).parent().parent().find('span').find(awicon).toggleClass('fa-eye fa-eye-slash');
		});
	},

	isImage: function(){
		var file = this[0].files[0]; var fileType = file["type"];
		var validImageTypes = ["image/gif", "image/jpeg", "image/png", "image/jpg"];
		if ($.inArray(fileType,validImageTypes)<0){return false;}else{return true;}
	}, 

	autoHeight: function () {
		function autoHeight_(element) {
		  return jQuery(element)
			.css({ 'height': 'auto', 'overflow-y': 'hidden' })
			.height(element.scrollHeight);
		}
		return this.each(function() {
		  autoHeight_(this).on('input', function() {
			autoHeight_(this);
		  });
		});
	},

	bShow: function(){
		$(this).removeClass('d-none');
	},

	bHide: function(){
		$(this).addClass('d-none');
	},

	toggleIcon: function(icon){
		this.find(awicon).toggleClass(icon);
	},

	validarCPF: function(){
		var Soma; var Resto; Soma = 0; strCPF = this.val().replace('-','').replace('.','').replace('.','');
		for(i=0; i<=9; i++){ var cpf = ''; for(cp=1;cp<=11;cp++){ cpf+=i; } if(cpf == strCPF){ return false; }}
		for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
		Resto = (Soma * 10) % 11;
		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
		Soma = 0;
		for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
		Resto = (Soma * 10) % 11;
		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
		return true;
	},

	LoadVincUser: function(maintipo){ var superuser = maintipo;
		this.keyup(function(){ var valor = $(this).val();
			clearTimeout(timer); 
			if ($(this).val().length > 2) {
				timer = setTimeout(function(){
					$('div#VincEstTable tbody').hide();
					$('div#VincEstTable tbody#UserSearch').show();
					$.post("/load.php/buscar-usuario",{userNomeBusca:valor,userRABusca:false,userTipoBusca:33},function(json){
						json = (JSON.parse(json)); 
						if(parseInt(Object.keys(json).length) > 0){
							$('div#VincEstTable tbody#UserBody').html('');
							$.each(json, function(key,valor){
								$('div#VincEstTable tbody#UserBody').append('<tr class="iVinc mpoint bg-success-hover border-dashed-start text-center bg-lfocus-hover ft11" data-id="'+valor.user_id+'">'+
								'<td class="align-middle pdtb-5 w-15">'+valor.ui_matricula+'</td>'+
								'<td class="align-middle pdtb-5 w-15">'+valor.ui_doc+'</td>'+
								'<td class="align-middle pdtb-5 w-15">'+valor.ui_nascimento.replace(/(\d*)-(\d*)-(\d*).*/, '$3/$2/$1')+'</td>'+
								'<td class="align-middle pdtb-5 text-left pdl-10">'+valor.ui_nome+'</td>'+
								'<td class="align-middle pdtb-5 w-5"><i class="fa fa-plus iTrash"></i><input type="hidden" name="vinc-'+valor.user_id+'" value="'+valor.user_id+'"></td>'+
								'</tr>');
							});
							$('div#VincEstTable tbody#Nobody').hide();
							$('div#VincEstTable tbody#UserBody').show();
						}else{
							$('div#VincEstTable tbody#Nobody').show();
							$('div#VincEstTable tbody#UserBody').hide();	
						}
						$('div#VincEstTable tbody#UserSearch').hide();
					});
				}, timeout);
			}
		});	
	},
});

jQuery.extend(jQuery,{
	LoadTurmaMap: function(){
		let Turma = $('#loadTurma').val();
		let viewEst = $('#viewEst');
		viewEst.find('optgroup').html(null);
		$.get("/load.php/TurmaEMap/"+Turma, function(dados,status){
			json = JSON.parse(dados);
			if(typeof json === 'object' && parseInt(Object.keys(json).length) > 0){
				$.each(json, function(key,valor){
					if(key != 0){
						viewEst.find('optgroup').append('<option value="'+valor.vt_user+'">'+valor.ui_nome+'</option>');
					}
				});
			}
		});
	},
});
	


/* Another Function */
function UniqID(){return '_' + Math.random().toString(36).substr(2, 9);}

function ECropie(arquivo,ratio=1,altsize=false){
	if(typeof($image_crop) == 'undefined'){
		largura = 300 * ratio;
		$image_crop = $('#image_demo').croppie({
			enableExif: true,
			viewport: {
				width:largura,
				height:300,
				type:'square', //circle
			},
			boundary:{height:400},
			enableResize: altsize,
		});
	}
	var reader = new FileReader();
	reader.onload = function(event){$image_crop.croppie('bind', {url: event.target.result});};
	reader.readAsDataURL(arquivo);
	$('#ECropie').modal('show');
};

function ECropieSend(onde){
	$image_crop.croppie('result', {
		type: 'canvas',
		size: 'viewport'
	}).then(function(response){
		$.ajax({
			url:"/load.php/altPic/"+onde,
			type: "POST",
			data:{"image": response},
			success:function(data)
			{if(data){
				if(onde=='MyPhoto'){$('img.UserPic').attr('src','/files/pic/'+data);}
				if(onde=='Logomarca'){$('#LogomarcaImage').attr('src','/files/logos/'+data); $('#LogoSCT').val(data);}
				$('#ECropie').modal('hide'); eac(true);
			}else{eac(false);}}
		});
	});
};

function MyFilesLoad(){
	const modal = $('div#ModalFiles');
	if(modal.length){ modal.modal('show');
		$.ajax({
			//type: 'GET',
			url : '/load.php/LoadMyFiles/',
			//data: {},
			success: function(data){
				
				json = JSON.parse(data); // CARREGA OS ITENS DO PHP
				let Painel = $('#ModalFilesBody .row'); // ELEMENTO PAINEL
				const Body = $('#ModalBodyHide').html(); // ELEMENTO BASE
				Painel.html(''); // LIMPA O PAINEL
				$.each(json, function(key,item){ // ATUALIZA AS INFORMAÇÕES E INSERE NO PAINEL
					let html = Body;
					html = html
						.replaceAll('{fl_id}',item.fl_id)
						.replaceAll('{fl_nome}',item.fl_nome)
						.replaceAll('{fl_download}',item.fl_download)
						.replaceAll('{fl_size}',item.fl_size)
						.replaceAll('{fl_data}',item.fl_data)
						.replaceAll('{fl_icon}',item.fl_icon)
						.replaceAll('{fl_icon_color}',item.fl_icon_color);
					Painel.prepend(html);
				});
				// EXIBE O PAINEL
				$(document).find('#ModalFilesBody,#ModalFilesLoad').toggle();
			}
		});
	}
	return false;
} 

/* jQuery Action Function */
$(function () { 

	$(document).on('click','button.AnexarFiles',function(){
		let main = $(this).parents('div.fileView');
		let mins = $("#MyAnexosFiles");
		let fid  = main.data('id');
		let nome = main.find('span.fileName').html();
		mins.append(''+
		'<div class="btn-group mb-1 ms-1">'+
		'	<span class="btn btn-sm btn-outline-danger">'+nome+'</span>'+
		'	<input type="hidden" name="fl-'+fid+'" value="'+fid+'">'+
		'	<span class="btn btn-sm btn-danger MyAnexedFile"><i class="fa fa-trash"></i></span>'+
		'</div>');
		$('#ModalFiles').modal('hide');
	});

	const MyFilesUploadAreaColor = 'text-secondary border-secondary';
	$(document).on('dragover','#MyFilesUploadArea',function(){$(this).removeClass(MyFilesUploadAreaColor);});
	$(document).on('dragleave','#MyFilesUploadArea',function(){$(this).addClass(MyFilesUploadAreaColor);});
	$(document).on('drop','#MyFilesUploadArea',function(event){
		event.preventDefault();
		event.stopPropagation();
		$(this).addClass(MyFilesUploadAreaColor);
	});
	$(document).on('click','span.MyAnexedFile',function(){ $(this).parents('div.btn-group').remove(); });
	$(document).on('mouseenter','div.fileView',function(){$(this).find('div.fileAction').toggleClass('opacity-25 opacity-100');});
	$(document).on('mouseleave','div.fileView',function(){$(this).find('div.fileAction').toggleClass('opacity-25 opacity-100');});

	$(document).on('click','#sidebarToggle',function(){
		$(this).find(awicon).toggleClass('fa-square-caret-left fa-bars');
	});

	$(document).on('click','[data-rmv]',function(){
		$('#ModalConfirmation').find('#ModalConfirmationLink').attr('href',('/rmv/' + $(this).data('rmv')));
		$('#ModalConfirmation').modal('show');
	});

	$(document).on('click','[data-cfm]',function(){
		$('#ModalConfirmationTrue').find('#ModalConfirmationTrueConfirm').attr('href', $(this).data('cfm'));
		$('#ModalConfirmationTrue').modal('show');
	})

	$(document).on('click','div.bFile[data-file] button.bTrash',function(){
		$(this).parents('div.bFile').remove();
	});

	$(document).on('click','#UserSearchButton',function(){
		let Aguarde = $('#ModalAguarde'); Aguarde.modal('show');
		let SeachNull = $('#UserSearchNull'); SeachNull.hide();
		let MyType = parseInt($('#userMyType').val());
		let form = $('#UserSearchForm');
		let lista = $('#UserSearchList'); 
			lista.hide(); lista.find('tbody').html('');

		$.ajax({
			type: "POST",
			url: '/load.php/buscar-usuario',
			data: form.serialize(), // serializes the form's elements.
			success: function(data){
				
				const users = JSON.parse(data);
				console.log(users);
				// SE FOR NULO, EXIBE O AVISO

				if(users.length == 0 || users[0] == 'false'){ SeachNull.fadeIn(200); }else{
					$.each(users,function(key,val){
						lista.find('tbody').prepend('<tr class="bg-lfocus-hover">'+
							'<td class="align-middle ps-2">' + val.ui_nome + '</td>'+
							'<td class="border-start text-center">'+
								'<a href="/secretaria/usuario/resetar-senha/'+val.user_id+'" class="btn btn-sm btn-warning py-0 mx-1 ft-10"><i class="fa fa-key"></i></a>'+
								'<a href="/secretaria/usuario/cadastro/'+val.user_id+'" class="btn btn-sm btn-success py-0 mx-1 ft-10"><i class="fa fa-edit"></i></a>'+
								(MyType==0?'<a href="/admin/superuser/'+val.user_id+'" class="btn btn-sm btn-dark py-0 mx-1 ft-10"><i class="fa fa-user-secret"></i></a>':'')+
							'</td>'+
						'</tr>');
					});
					lista.fadeIn(300);
				}

			}
		});
		Aguarde.modal('hide');
		$('#userNomeBusca').focus();
	});

	$('#userNomeBusca,#userRABusca').on('input',function(){
		clearTimeout(temporiza);
		temporiza = setTimeout(function(){
			$('#UserSearchButton').click();
		},500);
	});

	$(document).on('keyup','input#userRA',function(){
		let valor = $(this).val(); let ra = valor;
		for(let i=0; i < (8 - valor.length); i++){ ra = '0' + ra; }
		$('#UserNewVinc-Type').val(33);

		clearTimeout(temporiza);
		temporiza = setTimeout(function(){
			
			$.ajax({
				url:"/load.php/search-ra/",
				type: "POST",
				data:{"ra": ra},
				success:function(data)
				{
					let json = JSON.parse(data);
					if(json['user_id'] != 'undefined' && json[0] != false){
						$('#UserNewVinc-Turma').prop('disabled',false);
						$('#UserNewVinc-Nome').html(json['nome']);
						$('#UserNewVinc-ID').val(json['id']);
						$('#UserNewVinc').modal('show');
					}
				}
			});
			
		},1000);
	});

	$(document).on('keyup','input#userCPF,select#userTipo',function(){
		let valor = $('#userCPF').val().replaceAll('.','').replaceAll('-','');
		let uTipo = $('#userTipo').val(); // Capitura o tipo de usuario
		$('#UserNewVinc-Type').val(uTipo); // Define o tipo de usuario

		if(valor.length==11){
			clearTimeout(temporiza);
			temporiza = setTimeout(function(){
				
				$.ajax({
					url:"/load.php/search-doc/",
					type: "POST",
					data:{"doc": valor, "tipo": uTipo},
					success:function(data)
					{
						let json = JSON.parse(data);
						if(json[0] == 'error'){
							$('#ModalNewUserAdicional, #ModalNewUserPessoal, #ModalNewUserSubmit').hide();
							$('#ModalNewUserVinculo').show();
						}else{
							console.log(json);
							if(json[0] != false && json.length > 0 && json['id'] != 'undefined'){
								$('#UserNewVinc-Turma').prop('disabled',(uTipo==33?false:true));
								$('#UserNewVinc-Nome').html(json['nome']);
								$('#UserNewVinc-ID').val(json['id']);
								$('#UserNewVinc').modal('show');
							}
						}
					}
				});
						
			},1000);
		}
	});
	
	$(document).ready(function(){
		$(this).find('[data-lm-recursive]')
			.focusin(function(){ $(this).parents($(this).data('lm-recursive')).addClass('bg-lfocus'); })
			.focusout(function(){ $(this).parents($(this).data('lm-recursive')).removeClass('bg-lfocus'); });
	});

	// var temporiza;
	// $("#email").on("input", function(){
	//    clearTimeout(temporiza);
	//    temporiza = setTimeout(function(){
	// 	  alert("Chama Ajax");
	//    }, 3000);
	// });
	

});
