<?php
########################################################
#	Sistem Informasi Stok Farmasi Versi 1.1 (Maret 2017)
#	Dikembangkan untuk digunakan di SMK Farmasi Mahadhika 4 
#	Tidak untuk diperjualbelikan
#	Dikembangkan oleh : Ucu Suryadi (oetjoe.soerjadi@gmail.com) - http://ucu.suryadi.my.id
# 	Hak Cipta hanya milik Allah SWT
########################################################

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {die('Error , Contact oetjoe.soerjadi@gmail.com');};

if(isset($_POST['select'])){
	$id   = $_POST['id_barang'];
	$nama = $_POST['nama_barang'];
}

if(isset($_POST['keluar'])){
	$tgl       = $_POST['tgl'];
	$id_barang = $_POST['id_barang'];
	$qty       = $_POST['qty'];
	
	$cek = $db->prepare("SELECT id_barang, stok_tersedia FROM far_stock WHERE id_barang = ?");
	$cek->execute(array($id_barang));
	//$stok_sedia = null;
	while($fs = $cek->fetch()){
		$stok_sedia = $fs['stok_tersedia'];
	}
	
		if($qty < $stok_sedia){
			$brg_keluar = $db->prepare("INSERT INTO far_keluar SET tgl = ?, jml = ?, id_barang = ?");
			$brg_keluar->bindParam(1, $tgl);
			$brg_keluar->bindParam(2, $qty);
			$brg_keluar->bindParam(3, $id_barang);
			if(!$brg_keluar->execute()){
				print_r($brg_keluar->errorInfo());
			}else{
				echo "
					<div class=\"alert alert-success fade in\">
						<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>
						<strong><span style=\"color:#FF33CC\" class=\"glyphicon glyphicon-heart-empty\"></span>&nbsp;Transaksi berhasil</strong> Silahkan, lanjutkan pengisian data.
					</div>
				";
			}
			$keluar = $db->prepare("UPDATE far_stock SET keluar = keluar + ?, stok_tersedia = stok_tersedia - ? WHERE id_barang = ?");
			$keluar->bindParam(1, $qty);
			$keluar->bindParam(2, $qty);
			$keluar->bindParam(3, $id_barang);
			if(!$keluar->execute()){
				print_r($keluar->errorInfo());
			}else{
				echo "
					<div class=\"alert alert-success fade in\">
						<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>
						<strong><span style=\"color:#FF33CC\" class=\"glyphicon glyphicon-heart-empty\"></span>&nbsp;Transaksi berhasil</strong> Silahkan, lanjutkan pengisian data.
					</div>
				";
			}
		}else{
			echo "
					<div class=\"alert alert-danger fade in\">
						<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>
						<strong><span style=\"color:#FF33CC\" class=\"glyphicon glyphicon-heart-empty\"></span>&nbsp;Transaksi gagal</strong> Stok barang kurang.
					</div>
				";
		}	
}
?>
<div class="row">
	<div class="col-md-8">
		<div class="box box-success">
			<div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-hand-o-left fa-fw"></i>Transaksi Barang Keluar</h3>
            </div>
			<div class="box-body">
				<form method="post" action="" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Tanggal</label>
						<div class="col-sm-5">
							<input id="barang_tmbh" type="text" name="tgl" class="form-control" required />
						</div>
                    </div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Kode Barang</label>
						<div class="row">
							<div class="col-xs-3">
								<?php if(empty($id)){
								?>
								<input type="text" name="id_barang" class="form-control" readonly />
								<?php
								}else {
								?>
								<input type="text" name="id_barang" class="form-control" value="<?php echo $id ?>" readonly />
								<?php
								}
								?>
							</div>
							<div class="col-xs-2" style="padding:0px">
								<a href="#" class="btn btn-success btn-sm" data-toggle="modal" data-target="#AmbilBarang"><i class="fa fa-search-plus fa-fw"></i>Cari</a>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Nama Barang</label>
						<div class="col-sm-5">
							<?php if(empty($nama)){ ?>							
							<input type="text" name="nama_barang" class="form-control" readonly />
							<?php }else{ ?>														
							<input type="text" name="nama_barang" class="form-control" value="<?php echo $nama;?>" readonly />
							<?php } ?>													
						</div>
                    </div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Jml Barang Keluar</label>
						<div class="col-sm-2">
							<input type="number" name="qty" class="form-control" required />
						</div>
                    </div>
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" name="keluar" class="btn btn-success"><span class="glyphicon glyphicon-floppy-saved"></span>&nbsp;Simpan</button>&nbsp;<button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-floppy-remove"></span>&nbsp;Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!--AMBIL DATA BARANG -->
<div class="modal fade" id="AmbilBarang" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Pilih Data Barang</h4>
			</div>
			<div class="modal-body">
				<?php
					$brg = $db->prepare("SELECT far_barang.id_barang, far_barang.kode_barang, far_barang.nama_barang, far_barang.nama_kategori, far_barang.nama_satuan, far_stock.stok_tersedia FROM far_barang LEFT JOIN far_stock ON far_barang.id_barang = far_stock.id_barang GROUP BY far_barang.nama_barang");
					$brg->execute();
					$barang = $brg->fetchAll();	
					$urut = 1;
				?>
					<table id="Keluar" style="font-size:13px" class="table table-striped table-bordered">
						<thead>
							<tr>
								<th class="col-xs-1">Urut</th>
								<th>Kode Barang</th>
								<th>Nama Barang</th>
								<th>Kategori</th>
								<th>Stok</th>
								<th>Satuan</th>
								<th>Aksi</th>
							</tr>						
						</thead>				
						<tbody>							
							<?php foreach($barang as $barang){ ?>
							<tr>
								<td><?php echo $urut++ ?></td>
								<td><input type="hidden" name="id_barang" value="<?php echo $barang['id_barang'];?>" /><?php echo $barang['kode_barang'];?></td>
								<td><input type="hidden" name="nama_barang" value="<?php echo $barang['nama_barang'];?>" /><?php echo $barang['nama_barang'];?></td>
								<td><?php echo $barang['nama_kategori']?></td>
								<td><?php echo $barang['stok_tersedia'];?></td>
								<td><?php echo $barang['nama_satuan'];?></td>
								<td>
									<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#PilihBarang-<?php echo $barang['id_barang'];?>"><i class="fa fa-check-square-o fa-fw"></i>Pilih</button>									
								</td>
							</tr>
								<!--PILIH BARANG -->
								<div class="modal fade" id="PilihBarang-<?php echo $barang['id_barang'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h4 class="modal-title" id="myModalLabel">Pilih Data Barang</h4>
											</div>
											<form method="post" action="">
												<div class="modal-body">
													<div class="row">
													<div class="form-group col-sm-5">
														<label>ID Barang</label>
														<input type="text" name="id_barang" class="form-control" value="<?php echo $barang['id_barang'];?>" readonly />
													</div>
													<div class="form-group col-sm-5">
														<label>Nama Barang</label>
														<input type="text" name="nama_barang" class="form-control" value="<?php echo $barang['nama_barang'];?>" readonly />
													</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" name="select" class="btn btn-primary">Pilih Barang</button>
													<button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							<?php
								}
							?>							
						</tbody>
					</table>
			</div>
			<div class="modal-footer">				
				<button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>
<!--TABLE FILTER-->
<script src="assets/plugins/tablefilter/tablefilter.js"></script>
<script data-config>
var filtersConfigEs = {
        base_path: 'assets/plugins/tablefilter/',
		watermark: ['','','cari barang...'],
		enable_default_theme: true,
		paging: true,		
		results_per_page: ['Records: ', [10,25,50,100]],
        remember_grid_values: true,
        remember_page_number: true,
        remember_page_length: true,		
		rows_counter: true,
		btn_reset: true,
        btn_reset_text: 'Clear',
		col_0: 'none',		
		col_1: 'none',
		col_3: 'select',
		col_4: 'select',
		col_5: 'select',
		col_6: 'none',
		custom_options: {
            cols:[4],
            texts: [
                [
                    'na', '0 - 5', '5 - 20',
                    '20 - 50', '50 - 100', '100 - 150',
                    '150 - 200', '200 - 250', 'not na'
                ]
            ],
            values: [
                [
                    'na', '>0 && <=5', '>5 && <=20',
                    '>20 && <=50', '>50 && <=100',
                    '>100 && <=150', '>150 && <=200', '>200 && <=250', '!na'
                ]
            ],
            sorts: [false]
        },
		no_results_message: {
			content: '<h3>Tidak ada data</h3>'
		},
    };
	var tf = new TableFilter('Keluar', filtersConfigEs);    
    tf.init();
</script>