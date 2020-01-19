<?php

function main() {
    $data = load_data();
    $path = $_SERVER['REQUEST_URI'];

    $page = get_page( $data, $path );

    $template_data = [];
    $template_data['css'] = get_css( $data );
    $template_data['custom-css'] = get_custom_css( $data );
    $template_data['script'] = get_script( $data );
    $template_data['header'] = get_header( $data );
    $template_data['footer'] = get_footer( $data );

    if( ! $page ) {
        go_404( $data, $template_data );
        return;
    }

    $template_data['title'] = get_title( $data, $page );
    $template_data['content'] = get_content( $data, $path );

    echo make_html( $template_data );
}

function get_content( $data, $path ) {
    $page = get_page( $data, $path );

    if( ! array_key_exists( 'txti', $page ) ) {
        error_log( 'Sepcified page doesn\'t have a txti path defined in lofi-data.json.' );
        return '';
    }

    $content = get_txti( $page['txti'] );

    // display code blocks appropriately
    $dom = new DOMDocument;
    $dom->loadHTML( $content );
    $code_sections = $dom->getElementsByTagName( 'code' );
    foreach( $code_sections as $code ) {
        $code->textContent = htmlspecialchars_decode( $code->textContent );
    }
    
    return $dom->saveHTML();
}

function get_css( $data ) {
    if( ! array_key_exists( 'css', $data ) ) {
        return 'https://cdn.jsdelivr.net/gh/kognise/water.css@latest/dist/light.min.css';
    }

    if( $data['css'] === false ) {
        return '';
    }

    return $data['css'];
}

function get_custom_css( $data ) {
    if( ! array_key_exists( 'custom-css', $data ) ) {
        return '';
    }

    return sprintf( '<link rel="stylesheet" href="%s">', $data['custom-css'] );
}

function get_footer( $data ) {
    if( ! array_key_exists( 'footer', $data ) ) {
        return '';
    }
    
    return get_txti( $data['footer'] );
}

function get_header( $data ) {
    if( ! array_key_exists( 'header', $data ) ) {
        return '';
    }
    
    $header = get_txti( $data['header'] );
    $nav = make_nav( $data );
    return $header . $nav;
}

function get_page( $data, $path ) {
    if( ! array_key_exists( 'pages', $data ) ) {
        error_log( 'No pages found in lofi-data.json.' );
        return false;
    }

    $results = array_filter( $data['pages'], function( $page ) use ( $path ) {
        return $page['path'] === $path;
    } );

    if( empty( $results ) ) {
        error_log( 'Specified path not found in lofi-data.json.' );
        return false;
    }

    return array_pop( $results );
}

function get_script( $data ) {
    if( ! array_key_exists( 'script', $data ) ) {
        return '';
    }

    return sprintf( '<script src="%s"></script>', $data['script'] );
}

function get_title( $data, $page = null ) {
    $title = '';

    if( array_key_exists( 'title', $data ) ) {
        $title .= $data['title'];
    }

    if( $page && array_key_exists( 'title', $page ) ) {
        if( strlen( $title ) ) {
            $title .= ' - ';
        }

        $title .= $page['title'];
    }

    return $title;
}

function get_txti( $path ) {
    $content = file_get_contents( sprintf( 'http://txti.es/%s/html', $path ) );

    if( $content === false ) {
        error_log( "Unable to load `$path` content." );
    }

    return $content;
}

function go_404( $data, $template_data = [] ) {
    $template_data['title'] = get_title( $data );
    $template_data['content'] = '<h2>404: Page not found.</h2>';
    echo make_html( $template_data );
}

function load_data() {
    $contents = file_get_contents( 'lofi-data.json' );
    try {
        return json_decode( $contents, true );
    } catch(Exception $e) {
        echo 'Unable to load website data.';
    }
}

function make_html( $data ) {
    $body = '';

    if( $data['header'] ) {
        $body .= sprintf( '<header>%s</header>', $data['header'] );
    }

    if( $data['content'] ) {
        $body .= sprintf( '<main>%s</main>', $data['content'] );
    }

    if( $data['footer'] ) {
        $body .= sprintf( '<footer>%s</footer>', $data['footer'] );
    }

    $layout = <<<EOD
        <!DOCTYPE html>

        <html lang="en">

            <head>
                <meta charset="utf-8" />
                <meta name="viewport" content="initial-scale=1, width=device-width" />
                <title>%s</title>
                <link rel="stylesheet" href="%s" />
                %s
                <style>
                    .lofi-menu ul {
                        padding-left: 0;
                        list-style: none;
                    }

                    .lofi-menu li {
                        display: inline-block;
                    }

                    .lofi-menu li + li {
                        margin-left: 1rem;
                    }
                </style>
            </head>
            
            <body>
                %s
                %s
            </body>
            
        </html>
EOD;

    return sprintf(
        $layout,
        $data['title'],
        $data['css'],
        $data['custom-css'],
        $body,
        $data['script']
    );
}

function make_nav( $data ) {
    if( count( $data['pages'] ) === 1 ) {
        return '';
    }

    if( array_key_exists( 'menu', $data ) && $data['menu'] === false ) {
        return '';
    }

    $list = array_reduce( $data['pages'], function( $carry, $page ) {
        if( ! array_key_exists( 'path', $page ) ) {
            return $carry;
        }

        $path = $page['path'];
        $title = ucfirst( substr( $path, 1 ) );

        if( array_key_exists( 'title', $page ) ) {
            $title = $page['title'];
        }

        return sprintf( '%s<li><a href="%s">%s</a>', $carry, $path, $title );
    }, '' );

    $nav_attr = '';

    if( array_key_exists( 'menu', $data ) && $data['menu'] === 'horizontal' ) {
        $nav_attr = ' class="lofi-menu"';
    }

    return sprintf( '<nav%s><ul>%s</ul></nav>', $nav_attr, $list );
}

main();
