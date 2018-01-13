<?php
$model = new Model('shop_orders');
$items = $model->get_items(array('order' => 'order_date DESC'));
$item = array();
$item_names = array(
	'singular'=>'Order',
	'plural'=>'Orders'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
}

/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	
	$_POST['date'] = (!empty($_POST['date'])) ? date('Y-m-d', strtotime($_POST['date'])) : date('Y-m-d') ;
	$fields = $_POST;
	$add = 0;
	
	if(!empty($model->data['id'])){
		$result = $model->update($fields);
	}
	else{
		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}
	
	if($result !== FALSE){
		set_message('The '.$item_names['singular'].' has been '.($add ? 'added' : 'updated').' successfully.', 'success');
	}
	else{
		set_message('There was a problem '.($add ? 'adding' : 'updating').' the '.$item_names['singular'].'.', 'fail');
	}
	
	header('location:'.$module_url.($id ? 'edit/'.$id : ''));
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: DELETE
* ---------------------------------------------------------------------------*/
if($action == 'delete'){

	$result = $model->delete();
	if($result !== FALSE){
		set_message('The '.$item_names['singular'].' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$item_names['singular'].'.', 'fail');
	}
	
	header('location:'.$module_url);
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: ADD/EDIT
* ---------------------------------------------------------------------------*/
if($action == 'view') {
	$item = $model->data;
	$view = 'view';
	$rte = 1; // Include tinymce
	$uploader = 1; // Include uploadify
	$timestamp = time(); // Needed for uploadify
	$upload_dir = $config->get('admin_upload_img_dir').'news/';
}

ob_start();
?>

<?php
/* ----------------------------------------------------------------------------
* VIEW: EDIT
* ---------------------------------------------------------------------------*/
if($view == 'view'):
?>

<script type="text/javascript">
$(function() {
	function removeImage(file_ele, field, table, element) {
		var file = file_ele.val();
		if (window.confirm("Do you really want to delete this image?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:field, table:table, id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					element.html('');
					file_ele.val('');
				}
			});
		}
	}
	$('#paid').click(function(evt){
		evt.preventDefault();
		if(confirm('Are you sure you want to mark this order as PAID?')) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax.shop-orders', {action:'mark_paid',order_id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					$('#paid_status').html('<span style="color: #32CD32;">Yes</span>');
				}
			});
		}
	});
	$('#shipped').click(function(evt){
		evt.preventDefault();
		if(confirm('Are you sure you want to mark this order as SHIPPED?')) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax.shop-orders', {action:'mark_shipped',order_id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					$('#shipped_status').html('<span style="color: #32CD32;">Yes</span>');
				}
			});
		}
	});
	$('#del_card').click(function(evt){
		evt.preventDefault();
		if(confirm('Are you sure you want to DELETE this credit card data?')) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax.shop-orders', {action:'del_card',order_id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					$('#card_data_table').html('<caption><strong>Credit Card Info</strong></caption><tr><td>Card data has expired.</td></tr>');
				}
			});
		}
	});
});
</script>
<style>
	.cms_table th {
		text-align: right;
	}
	.cms_table caption {
		font-size: 1.4em;
		margin: 30px 0 10px;
	}
</style>

<?php
	$today = new DateTime();
	$expdate = new DateTime($item['order_date']);
	$expdate->add(new DateInterval('P7D'));
	
	$interval = $today->diff($expdate);
	$days_till_exp = $interval->format('%a');
?>

<h1><?php echo ucfirst($action); ?> Order Data</h1>
	<br>
	<h3>Order ID: SP-<?php echo sprintf( '%06d', $item['id'] ); ?></h3>
	<div class="wide_col">
		<table>
			<tr>
				<td valign="top">
					<table class="cms_table" id="" style="margin-right: 20px;">
						<caption><strong>Order Status</strong></caption>
							<tr>
								<th>Paid</th>
								<td id="paid_status"><?php echo ($item['paid']) ? '<span style="color: #32CD32;">Yes</span>' : '<span style="color: red;">No</span>' ; ?></td>
								<td style="text-align: center;"><a href="#paid" id="paid">Mark as Paid</a></td>
							</tr>
							<tr>
								<th>Shipped</th>
								<td id="shipped_status"><?php echo ($item['shipped']) ? '<span style="color: #32CD32;">Yes</span>' : '<span style="color: red;">No</span>' ; ?></td>
								<td style="text-align: center;"><a href="#shipped" id="shipped">Mark as Shipped</a></td>
							</tr>
					</table>
				</td>
				<td valign="top">
					<table class="cms_table" id="card_data_table">
						<caption><strong>Credit Card Info</strong></caption>
						<?php if(!empty($item['ccNum'])): ?>
							<tr>
								<td colspan="2">This credit card data will be automatically deleted <?php echo ($days_till_exp > 0) ? 'in <strong style="color: red;">'.$days_till_exp.'</strong> days' : '<strong style="color: red;">TODAY</strong>' ; ?>!</td>
							</tr>
							<tr>
								<th>Name</th>
								<td><?php echo $item['Billing_First_Name'].' '.$item['Billing_Last_Name']; ?></td>
							</tr>
							<tr>
								<th>Card Type</th>
								<td><?php echo $item['Cardname']; ?></td>
							</tr>
							<tr>
								<th>Card Number</th>
								<td><?php echo pdecrypt($item['ccNum']); ?></td>
							</tr>
							<tr>
								<th>Expiration</th>
								<td><?php echo $item['expMon'].'/'.$item['expYear']; ?></td>
							</tr>
							<tr>
								<th>CVV</th>
								<td><?php echo $item['CVV']; ?></td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;"><a href="#delete-card" id="del_card">Delete Credit Card Data</a></td>
							</tr>
						<?php else: ?>
							<tr>
								<td>Card data has expired.</td>
							</tr>
						<?php endif; ?>
					</table>
				</td>
			</tr>
		</table>
		<table class="cms_table">
			<caption><strong>Cart Data</strong></caption>
			<tr>
				<th>Item Name</th>
				<th>Color</th>
				<th>Size</th>
				<th>Price (each)</th>
				<th>Quantity</th>
				<th>Price (total)</th>
			</tr>
			<?php $cartTotal = 0; ?>
			<?php $cartData = json_decode($item['cart_data']); ?>
			<?php foreach($cartData->items AS $k => $i): ?>
				<?php 
					$itemTotal = ($i->product_price*$i->quantity);
					$cartTotal += $itemTotal; 
				?>
				<tr>
					<td><?php echo $i->product_title; ?></td>
					<td><?php echo (isset($i->color)) ? $i->color : '' ; ?></td>
					<td><?php echo (isset($i->color)) ? $i->size : '' ; ?></td>
					<td align="right"><?php echo '$'.number_format($i->product_price, 2, '.', ''); ?></td>
					<td align="center"><?php echo $i->quantity; ?></td>
					<td align="right"><?php echo '$'.number_format(($i->product_price*$i->quantity), 2, '.', ''); ?></td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<td colspan="5" align="right"><strong>Sub Total:</strong></td>
				<td align="right">$<?php echo number_format($item['sub_total'], 2, '.', ''); ?></td>
			</tr>
			<?php if($item['discount_amount'] != 0): ?>
				<tr>
					<td colspan="5" align="right">[ CODE: <?php echo $item['promocode']; ?> ] <strong> <?php echo ($item['discount_perc'] != 0) ? ($item['discount_perc']*100).'% ' : '' ; ?>Promo Dicount:</strong></td>
					<td align="right" style="color: red;">-$<?php echo number_format($item['discount_amount'], 2, '.', ''); ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<td colspan="5" align="right"><strong>Tax:</strong></td>
				<td align="right">$<?php echo number_format($item['tax'], 2, '.', ''); ?></td>
			</tr>
			<tr>
				<td colspan="5" align="right"><strong>Shipping:</strong></td>
				<td align="right">$<?php echo number_format($item['shipping_price'], 2, '.', ''); ?></td>
			</tr>
			<tr>
				<td colspan="5" align="right"><strong>Total:</strong></td>
				<td align="right" style="background-color: #D7FFD0;">$<?php echo number_format($item['total'], 2, '.', ''); ?></td>
			</tr>
		</table>
		<table>
			<tr>
				<td valign="top">
					<table class="cms_table" id="" style="margin-right: 20px;">
						<caption><strong>Billing / Contact Info</strong></caption>
						<tr>
							<th>First Name</th>
							<td><?php echo $item['Billing_First_Name']; ?></td>
						</tr>
						<tr>
							<th>Last Name</th>
							<td><?php echo $item['Billing_Last_Name']; ?></td>
						</tr>
						<tr>
							<th>Address</th>
							<td><?php echo $item['Billing_Address']; ?></td>
						</tr>
						<tr>
							<th>City</th>
							<td><?php echo $item['Billing_City']; ?></td>
						</tr>
						<tr>
							<th>State</th>
							<td><?php echo $item['Billing_State']; ?></td>
						</tr>
						<tr>
							<th>Zip</th>
							<td><?php echo $item['Billing_Zip']; ?></td>
						</tr>
						<tr>
							<th>Phone</th>
							<td><?php echo $item['Phone']; ?></td>
						</tr>
					</table>
				</td>
				<td valign="top">
					<table class="cms_table" id="">
						<caption><strong>Shipping Info</strong></caption>
						<tr>
							<th>Shipping Method</th>
							<td><?php echo $item['shipping_method']; ?></td>
						</tr>
						<tr>
							<th>Location Type</th>
							<td><?php echo $item['Location_Type']; ?></td>
						</tr>
						<?php if($item['Same_Shipping_Address'] == 'Yes'): ?>
							<tr>
								<th>Ship to Billing Address?</th>
								<td><?php echo $item['Same_Shipping_Address']; ?></td>
							</tr>
						<?php else: ?>
							<tr>
								<th>First Name</th>
								<td><?php echo $item['Shipping_First_Name']; ?></td>
							</tr>
							<tr>
								<th>Last Name</th>
								<td><?php echo $item['Shipping_Last_Name']; ?></td>
							</tr>
							<tr>
								<th>Address</th>
								<td><?php echo $item['Shipping_Address']; ?><?php echo (!empty($item['Shipping_Address2'])) ? '<br>'.$item['Shipping_Address2'] : '' ; ?></td>
							</tr>
							<tr>
								<th>City</th>
								<td><?php echo $item['Shipping_City']; ?></td>
							</tr>
							<tr>
								<th>State</th>
								<td><?php echo $item['Shipping_State']; ?></td>
							</tr>
							<tr>
								<th>Zip</th>
								<td><?php echo $item['Shipping_Zip']; ?></td>
							</tr>
						<?php endif; ?>
					</table>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="clear"><!-- x --></div>

<?php
/* ----------------------------------------------------------------------------
* VIEW: LIST (DEFAULT)
* ---------------------------------------------------------------------------*/
else:
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#news_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>

<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="news_list">
  <thead>
  <tr>
  	<th>Order ID</th>
  	<th>First Name</th>
  	<th>Last Name</th>
  	<th>Email</th>
  	<th>Total</th>
  	<th>Order Date</th>
  	<th>Paid</th>
  	<th>Shipped</th>
  	<th>Payment Expiration</th>
   <th>Actions</th>
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<?php
		$today = new DateTime();
		$expdate = new DateTime($i['order_date']);
		$expdate->add(new DateInterval('P7D'));
		
		$interval = $today->diff($expdate);
		$days_till_exp = $interval->format('%a');
	?>
	
	<tr>
		<td>SP-<?php echo sprintf( '%06d', $i['id'] ); ?></td>
		<td><?php echo $i['Billing_First_Name']; ?></td>
		<td><?php echo $i['Billing_Last_Name']; ?></td>
		<td><?php echo $i['Email']; ?></td>
		<td><?php echo '$'.$i['total']; ?></td>
		<td><?php echo date('m/d/Y h:i A', strtotime($i['order_date'])); ?></td>
		<td style="text-align: center;"><?php echo ($i['paid'] == 1) ? '<span style="color: #32CD32;">Yes</span>' : '<span style="color: red;">No</span>' ; ?></td>
		<td style="text-align: center;"><?php echo ($i['shipped'] == 1) ? '<span style="color: #32CD32;">Yes</span>' : '<span style="color: red;">No</span>' ; ?></td>
		<td style="text-align: center;"><?php echo (!empty($i['ccNum']) && $days_till_exp >= 0) ? '<strong style="color: red;">'.$days_till_exp.'</strong> days' : 'Expired' ; ?></td>
		<td align="center">
			<a href="<?php echo $module_url.'view/'.$i['id']; ?>">View</a>
		</td>
	</tr>
	
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>

<?php
/* ----------------------------------------------------------------------------
* END VIEWS
* ---------------------------------------------------------------------------*/
endif;
?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>