<?php

function wmcz_admin_register() {
	add_menu_page('WMCZ', 'WMCZ', 'manage_options', 'wmcz', 'wmcz_admin_index');
	add_submenu_page('wmcz', 'WMCZ events', 'WMCZ events', 'manage_options', 'wmcz_events', 'wmcz_admin_events');
	add_submenu_page('wmcz', 'WMCZ news', 'WMCZ news', 'manage_options', 'wmcz_news', 'wmcz_admin_news');
}

function wmcz_admin_index() {
	echo "<h1>Hello world</h1>";
}

function wmcz_admin_events() {
	echo "<h1>Hello world</h1>";
}

function wmcz_admin_news_added() {
	?>
	<div class="updated notice">
	    <p>Novinka byla přidána.</p>
	</div>
	<?php
}

function wmcz_admin_news_edited() {
	?>
	<div class="updated notice">
	    <p>Změny v novinkách byly úspěšně uloženy.</p>
	</div>
	<?php
}

function wmcz_admin_news() {
	global $wpdb;

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		if ( $_POST['type'] === "new" ) {
			$wpdb->insert(
				$wpdb->prefix . "wmcz_news",
				[
					'title' => $_POST['title'],
					'description' => $_POST['description']
				]
			);
	
			wmcz_admin_news_added();
		} elseif ( $_POST['type'] === "update" ) {
			$news =  $wpdb->get_results( "SELECT id, title, description, photo, published FROM {$wpdb->prefix}wmcz_news", OBJECT );
			foreach ( $news as $new ) {
				if ( $new->title != $_POST["title$new->id"] ) {
					$data['title'] = $_POST["title-$new->id"];
				}
				if ( $new->description != $_POST["description-$new->id"] ) {
					$data['description'] = $_POST["description-$new->id"];
				}
				if ( isset( $data ) ) {
					$wpdb->update(
						$wpdb->prefix . "wmcz_news",
						$data,
						[
							'id' => $new->id
						]
					);
					wmcz_admin_news_edited();
				}
			}
		}
	}

	$news =  $wpdb->get_results( "SELECT id, title, description, photo, published FROM {$wpdb->prefix}wmcz_news", OBJECT );
	?>
	<h1>WMCZ news management</h1>
	<h2>Stávající novinky</h2>
	<form method="POST">
		<input type="hidden" name="type" value="update">
		<table>
			<thead>
				<tr>
					<th>Titulek</th>
					<th>Popisek</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach( $news as $new ): ?>
				<tr>
					<td>
						<input type="text" name="title-<?php echo $new->id ?>" value="<?php echo $new->title ?>">
					</td>
					<td>
						<textarea name="description-<?php echo $new->id ?>"><?php echo $new->description ?></textarea>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="submit" value="Potvrdit změny">
	</form>

	<h2>Přidat novou novinku</h2>
	<form method="POST">
		<input type="hidden" name="type" value="new">
		<label for="wmcz-news-new-title">Titulek</label>
		<input type="text" name="title" id="wmcz-news-new-title" />
		<label for="wmcz-news-new-description">Popisek</label>
		<textarea name="description" id="wmcz-news-new-description"></textarea>
		<input type="submit" value="Přidat" />
	</form>
	<?php
}

add_action( 'admin_menu', 'wmcz_admin_register' );
