<?php

function wmcz_admin_register() {
    add_menu_page('WMCZ', 'WMCZ', 'edit_published_pages', 'wmcz', 'wmcz_admin_index');
    add_submenu_page('wmcz', 'WMCZ caurosel', 'WMCZ caurosel', 'edit_published_pages', 'wmcz_caurosel', 'wmcz_admin_caurosel');
    add_submenu_page('wmcz', 'Manage cache', 'Manage cache', 'edit_published_pages', 'wmcz_cache_manage', 'wmcz_admin_cache_manage');
}

function wmcz_admin_index() {
    echo "<h1>Hello world</h1>";
}

function wmcz_admin_cache_manage() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        wmcz_admin_cache_manage_purged();

        $dataDir = dirname( __FILE__ ) .  '/data/';
        foreach ( scandir($dataDir) as $fileName ) {
            if (in_array( $fileName, [ '.', '..', '.gitkeep' ] )) {
                continue;
            }

            unlink( $dataDir . $fileName );
        }
    }

    ?>
    <form method="post">
        <h1><?php _e('Manage cache', 'wmcz-plugin'); ?></h1>
        <p>
            <?php _e('This will purge cache managed by the WMCZ plugin. This includes mainly calendar data.', 'wmcz-plugin'); ?>
        </p>
        <p>
            <?php _e('Please visit the list of events after purging the cache. The first request (which re-generates the cache) can take up to several seconds.', 'wmcz-plugin') ?>
        </p>
        <button type="submit"><?php _e('Purge cache', 'wmcz-plugin'); ?></button>
    </form>
    <?php
}

function wmcz_admin_cache_manage_purged() {
    ?>
    <div class="updated notice">
        <p><?php _e('Cache has been purged.', 'wmcz-plugin'); ?></p>
    </div>
    <?php
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
        $myPost = wp_unslash( $_POST );
        if ( $myPost['type'] === 'new' ) {
            $wpdb->insert(
                $wpdb->prefix . "wmcz_caurosel",
                [
                    'name' => $myPost['name'],
                    'description' => $myPost['description'],
                    'published' => isset( $myPost['published'] ),
                    'photo_id' => (int)$myPost['image'],
                    'link' => $myPost['link'],
                    'ordering_key' => (int)$myPost['orderingKey']
                ]
            );
            wmcz_admin_caurosel_added();
        } elseif ( $myPost['type'] === 'update' ) {
            $updated = false;
            $events = $wpdb->get_results( "SELECT id, name, description, published, photo_id FROM {$wpdb->prefix}wmcz_caurosel", OBJECT );

            if ( isset($myPost['delete']) ) {
                foreach ( $myPost['delete'] as $toDelete ) {
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
                $published = isset( $myPost["published-{$event->id}"] );

                if ( $myPost["name-{$event->id}"] !== $event->name ) {
                    $data['name'] = $myPost["name-{$event->id}"];
                }
                if ( $myPost["description-{$event->id}"] !== $event->description ) {
                    $data['description'] = $myPost["description-{$event->id}"];
                }
                if ( $published !== $event->published ) {
                    $data['published'] = $published;
                }
                if ( $myPost["link-{$event->id}"] !== $event->link ) {
                    $data['link'] = $myPost["link-{$event->id}"];
                }
                if ( $myPost["orderingKey-{$event->id}"] !== $event->ordering_key ) {
                    $data['ordering_key'] = $myPost["orderingKey-{$event->id}"];
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

    $events = $wpdb->get_results( "SELECT id, added, name, description, published, photo_id, link, ordering_key FROM {$wpdb->prefix}wmcz_caurosel ORDER BY ordering_key DESC, added DESC", OBJECT );
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
                    <th>Pořadí?</th>
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
                        <?php wp_editor( $event->description, "description-{$event->id}", [ 'textarea_rows' => 3 ] ); ?>
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
                        <input type="number" name="orderingKey-<?php echo $event->id; ?>" value="<?php echo $event->ordering_key?>">
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
                <?php wp_editor( "", "description", [ 'textarea_rows' => 3 ] ); ?>
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
                <th>Pořadí</th>
                <td>
                    <input type="number" name="orderingKey" value="0">
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