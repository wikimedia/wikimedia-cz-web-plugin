<?php

function wmcz_admin_register() {
    add_menu_page('WMCZ', 'WMCZ', 'edit_published_pages', 'wmcz', 'wmcz_admin_index');
    add_submenu_page('wmcz', 'WMCZ caurosel', 'WMCZ caurosel', 'edit_published_pages', 'wmcz_caurosel', 'wmcz_admin_caurosel');
}

function wmcz_admin_index() {
    echo "<h1>Hello world</h1>";
}

function wmcz_admin_caurosel_added() {
    ?>
    <div class="updated notice">
        <p>Položka do cauroselu byla přidána.</p>
    </div>
    <?php
}

function wmcz_admin_caurosel_edited() {
    ?>
    <div class="updated notice">
        <p>Položka do cauroselu byla upravena.</p>
    </div>
    <?php
}

function wmcz_admin_caurosel() {
    global $wpdb;
    wp_enqueue_media();
    wp_enqueue_script('wmcz-plugin-events', plugins_url( 'static/admin/events.js', __FILE__ ) );

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        if ( $_POST['type'] === 'new' ) {
            $wpdb->insert(
                $wpdb->prefix . "wmcz_caurosel",
                [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'published' => isset( $_POST['published'] ),
                    'photo_id' => (int)$_POST['image'],
                    'link' => $_POST['link'],
                ]
            );
            wmcz_admin_caurosel_added();
        } elseif ( $_POST['type'] === 'update' ) {
            $updated = false;
            $events = $wpdb->get_results( "SELECT id, name, description, published, photo_id FROM {$wpdb->prefix}wmcz_caurosel", OBJECT );

            if ( isset($_POST['delete']) ) {
                foreach ( $_POST['delete'] as $toDelete ) {
                    $wpdb->delete(
                        "{$wpdb->prefix}wmcz_caurosel",
                        [
                            'id' => (int)$toDelete
                        ]
                    );
                    $updated = true;
                }
            }

            foreach ( $events as $event ) {
                $data = [];
                $published = isset( $_POST["published-{$event->id}"] );

                if ( $_POST["name-{$event->id}"] !== $event->name ) {
                    $data['name'] = $_POST["name-{$event->id}"];
                }
                if ( $_POST["description-{$event->id}"] !== $event->description ) {
                    $data['description'] = $_POST["description-{$event->id}"];
                }
                if ( $published !== $event->published ) {
                    $data['published'] = $published;
                }
                if ( $_POST["link-{$event->id}"] !== $event->link ) {
                    $data['link'] = $_POST["link-{$event->id}"];
                }

                if ( count( $data ) > 0 ) {
                    $wpdb->update(
                        "{$wpdb->prefix}wmcz_caurosel",
                        $data,
                        [
                            'id' => $event->id
                        ]
                    );
                    $updated = true;
                }
            }

            if ( $updated ) {
                wmcz_admin_caurosel_edited();
            }
        }
    }

    $events = $wpdb->get_results( "SELECT id, added, name, description, published, photo_id, link FROM {$wpdb->prefix}wmcz_caurosel ORDER BY added DESC", OBJECT );
    ?>
    <h1>WMCZ Events management</h1>
    <form method="post">
        <input type="hidden" name="type" value="update">
        <table>
            <thead>
                <tr>
                    <th>Jméno</th>
                    <th>Popisek</th>
                    <th>Přidán</th>
                    <th>Publikován?</th>
                    <th>photo_id</th>
                    <th>Odkaz</th>
                    <th>Smazat?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $events as $event ): ?>
                <tr>
                    <td>
                        <input type="text" name="name-<?php echo $event->id ?>" value="<?php echo $event->name ?>">
                    </td>
                    <td>
                        <textarea name="description-<?php echo $event->id ?>" cols="50" rows="3"><?php echo $event->description ?></textarea>
                    </td>
                    <td>
                        <?php echo $event->added ?>
                    </td>
                    <td>
                        <input type="checkbox" name="published-<?php echo $event->id ?>" <?php if( $event->published ): ?>checked<?php endif; ?>>
                    </td>
                    <td>
                        <?php echo $event->photo_id ?>
                    </td>
                    <td>
                        <input type="text" name="link-<?php echo $event->id ?>" value="<?php echo $event->link ?>">
                    </td>
                    <td>
                        <input type="checkbox" name="delete[]" value="<?php echo $event->id ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <input type="submit" value="Odeslat">
    </form>
    <h2>Přidat novou akci</h2>
    <form method="post">
        <input type="hidden" name="type" value="new">
        <table>
            <tr>
                <th>jméno</th>
                <td><input type="text" name="name"></td>
            </tr>
            <tr>
                <th>popisek</th>
                <td>
                    <textarea name="description" cols="50" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <th>obrázek</th>
                <td>
                    <button type="button" data-input-name="image" class="button wmcz-events-image-selector">Vybrat obrázek</button>
                    <input type="hidden" name="image">
                </td>
            </tr>
            <tr>
                <th>odkaz</th>
                <td>
                    <input type="text" name="link" placeholder="http://wikimedia.cz/udalost" />
                </td>
            </tr>
            <tr>
                <th>publikován</th>
                <td>
                    <input type="checkbox" name="published" id="">
                </td>
            </tr>
        </table>
        <input type="submit" value="Odeslat">
    </form>
    <?php
}

add_action( 'admin_menu', 'wmcz_admin_register' );