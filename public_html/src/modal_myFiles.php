<div class="modal" tabindex="-1" id="ModalFiles">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">

			<div class="modal-header text-bg-primary text-white ft-10">
				<span class="modal-title"><i class="fa fa-file"></i> MEUS ARQUIVOS</span>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body py-1 border-bottom">
				<div class="row">
					<div class="col-12 text-end">
						<button type="button" class="btn btn-primary btn-sm" id="ModalMyFilesBtnNew"><i class="fa fa-file-circle-plus me-1"></i> NOVO ARQUIVO</button>
						<button type="button" class="btn btn-dark btn-sm d-off" id="ModalMyFilesBtnList"><i class="fa fa-list me-1"></i> LISTA DE ARQUIVO</button>
					</div>
				</div>
			</div>

			<div class="modal-body" id="ModalFilesLoad" style="max-height:80vh;">
				<div class="py-5 m-2 border rounded text-center text-bg-gray-500">
					<i class="fas fa-spinner fa-2x mb-2 fa-pulse"></i><br>Carregando ...
				</div>
			</div>

			<div class="modal-body d-off overflow-auto" id="ModalFilesBody" style="max-height:80vh;">
				<div class="row"></div>
			</div>
			
			<div class="d-none" id="ModalBodyHide">
				<div class="col-12 col-sm-6 col-md-3 mb-3 text-center">
					<div class="card fileView" data-id="{fl_id}">
						<div class="border p-2 rounded h-100 shadow-md border-{fl_icon_color}  bg-lfocus-hover">
							<div class="row fileInfo">
								<div class="col-5 col-sm-4 col-md-3 align-self-center text-start"><i class="fa fa-{fl_icon} text-{fl_icon_color} ft-16"></i></div>
								<div class="col-7 col-sm-8 col-md-9 align-self-center text-end ft-8">{fl_data} | {fl_size}</div>
								<div class="col-12"><hr class="my-1"><span class="ft-9 fileName">{fl_nome}</span></div>
							</div>
						</div>
						<div class="card-footer fileAction mt-1 pt-1 opacity-25">
							<a href="/files/{fl_download}" target="_blank" download="{fl_nome}" class="btn-sm btn btn-secondary py-0 ft-9"><i class="fa fa-file-arrow-down me-1"></i> DOWNLOAD</a>
							<button class="btn btn-sm btn-primary ft-9 py-0 AnexarFiles"><i class="fa fa-paperclip me-1"></i> ANEXAR</button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal-body border-top text-center d-off" id="ModalFilesNew">

				<form class="form" action="#" method="post">
					<div class="fv-row">
						<div class="dropzone" id="kt_dropzonejs">
							<div class="dz-message needsclick">
								<i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>

								<div class="ms-4">
									<p class="text-center"><i class="fa fa-cloud-arrow-up fa-3x"></i></p>
									<h3 class="fs-5 mb-1">Solte os arquivos aqui ou clique para fazer upload.</h3>
									<span class="fs-7 text-gray-500">(pdf, doc, docx ou imagens de no máximo 2 Mb)</span>
								</div>
							</div>
						</div>
					</div>
				</form>

			</div>

			
		</div>
	</div>
</div>

<script>
	$(function() {
		//$('#ModalFiles').modal('show');

		/* Altern Button and Panel */
		$('#ModalMyFilesBtnNew,#ModalMyFilesBtnList').click(function() {
			$('#ModalFilesNew,#ModalFilesBody').toggle(300);
			$('#ModalMyFilesBtnNew,#ModalMyFilesBtnList').toggle(300);
		});

		/* Initialize DropZone Upload */
		var myDropzone = new Dropzone("#kt_dropzonejs", {
			url: "/load.php/dropzone", // Set the url for your upload script location
			paramName: "file", // The name that will be used to transfer the file
			maxFiles: 5,
			maxFilesize: 2, // MB
			addRemoveLinks: true,
			//acceptedFiles: ".jpg,.jpge,.png,.pdf,.doc,.docx",
			init: function() {
				this.on("sending", function(file, xhr, formData) {
					formData.append("local", DropZoneLocation);
				});
			},
			accept: function(file, done) {
				done();
			},
			success: function(file, response) {
				
				json = JSON.parse(response);
				let mins = $("#MyAnexosFiles");
				if(mins.length > 0){
					mins.append(''+
					'<div class="btn-group mb-1 ms-1">'+
					'	<span class="btn btn-sm btn-outline-danger">'+json.fl_nome+'</span>'+
					'	<input type="hidden" name="fl-'+json.id+'" value="'+json.id+'">'+
					'	<span class="btn btn-sm btn-danger MyAnexedFile"><i class="fa fa-trash"></i></span>'+
					'</div>');
					$('#ModalFiles').modal('hide');
				}

			}
		});
	});
</script>