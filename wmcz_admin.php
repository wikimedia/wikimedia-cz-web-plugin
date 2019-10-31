<?php

function wmcz_admin_register() {
	add_menu_page('WMCZ', 'WMCZ', 'manage_options', 'wmcz', 'wmcz_admin_index');
	add_submenu_page('wmcz', 'WMCZ tags', 'WMCZ tags', 'manage_options', 'wmcz_tags', 'wmcz_admin_tags');
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

function wmcz_admin_tags_added() {
	?>
	<div class="updated notice">
		<p>Štítek byl přidán.</p>
	</div>
	<?php
}

function wmcz_admin_tags_edited() {
	?>
	<div class="updated notice">
		<p>Změny ve štítkách byly úspěšně uloženy.</p>
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
			$updated = false;
			
			if ( isset($_POST['delete']) ) {
				foreach ( $_POST['delete'] as $toDelete ) {
					$wpdb->delete(
						"{$wpdb->prefix}wmcz_news",
						[
							'id' => (int)$toDelete
						]
					);
					$updated = true;
				}
			}

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
                    $updated = true;
				}
            }
            if ( $updated ) {
                wmcz_admin_news_edited();
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
					<th>Smazat?</th>
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
					<td>
						<input type="checkbox" name="delete[]" value="<?php echo $new->id ?>">
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

function wmcz_admin_tags() {
	global $wpdb;

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		if ( $_POST['type'] == "new" ) {
			$wpdb->insert(
				$wpdb->prefix . "wmcz_tags",
				[
					'name' => $_POST['name-new']
				]
			);
			wmcz_admin_tags_added();
		} elseif ( $_POST['type'] == "update" ) {
			$updated = false;
			$tags = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wmcz_tags", OBJECT );

			if ( isset($_POST['delete']) ) {
				foreach ( $_POST['delete'] as $toDelete ) {
					$wpdb->delete(
						"{$wpdb->prefix}wmcz_tags",
						[
							'id' => (int)$toDelete
						]
					);
					$updated = true;
				}
			}

			foreach ( $tags as $tag ) {
				$data = [];
				if ( $_POST["name-$tag->id"] != $tag->name ) {
					$data['name'] = $_POST["name-$tag->id"];
				}

				if ( count($data) > 0 ) {
					$wpdb->update(
						"{$wpdb->prefix}wmcz_tags",
						$data,
						[
							'id' => $tag->id
						]
					);
					$updated = true;
				}
			}

			if ( $updated ) {
				wmcz_admin_tags_edited();
			}
		}
	}

	$tags = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wmcz_tags", OBJECT );
	?>
	<h1>WMCZ Tags management</h1>
	<h2>Stávající štítky</h2>
	<form method="post">
		<input type="hidden" name="type" value="update">
		<table>
			<thead>
				<tr>
					<th>jméno</th>
					<th>smazat?</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach( $tags as $tag ): ?>
				<tr>
					<td>
						<input type="text" name="name-<?php echo $tag->id ?>" value="<?php echo $tag->name ?>">
					</td>
					<td>
						<input type="checkbox" name="delete[]" value="<?php echo $tag->id ?>">
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="submit" value="Potvrdit změny">
	</form>

	<h2>Přidat novou položku</h2>
	<form method="post">
		<input type="hidden" name="type" value="new">
		<table>
			<thead>
				<tr>
					<th>jméno</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="text" name="name-new"></td>
				</tr>
			</tbody>
		</table>
		<input type="submit" value="Přidat položku">
	</form>
	<?php
}

add_action( 'admin_menu', 'wmcz_admin_register' );
