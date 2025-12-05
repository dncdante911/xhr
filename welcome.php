<?php
if ($f == 'welcome') {
    if ($s == 'load_authentication') {
        $wo['page'] = 'welcome';
        if(empty($_POST['auth_type'])) {
            return false;
        }

        $html_sidebar = '';
        if($_POST['auth_type'] == 'login') {
            $html_sidebar = Wo_LoadPage('welcome/login_html');
        }

        $data = array(
            'status' => 200,
            'auth_name' => $_POST['auth_type'],
            'auth_layout' => $html_sidebar, 
        );
    }

    if ($s == 'load_sidebar') {
        $wo['page'] = 'welcome';
        if(empty($_POST['tab_name'])) {
            return false;
        }

        $html_sidebar = '';
        if($_POST['tab_name'] == 'explore') {
            $html_sidebar = Wo_LoadPage('welcome/explore');
        }else if($_POST['tab_name'] == 'following') {
            $html_sidebar = Wo_LoadPage('welcome/users_following');
        }else if($_POST['tab_name'] == 'pro_member') {
            $html_sidebar = Wo_LoadPage('welcome/pro_members');
        }else if($_POST['tab_name'] == 'reels') {
            $wo['config']['second_post_button'] = 'disabled';
            $wo['watched_reels'] = array();
            $html = '';
            $main_reel = 0;

            $reelOwnerName = '';
            $getPosts = false;
            $postsData = array(
                'limit' => 4,
                'filter_by' => 'local_video',
                'order' => 'rand',
                'is_reel' => 'only'
            );

            if (empty($_GET['id'])) {
                $getPosts = true;
            }

            if (!empty($wo['watched_reels']) && empty($_GET['id'])) {
                $postsData['not_in'] = $wo['watched_reels'];
            }

            $reels = [];

            if ($getPosts) {
                $reels = Wo_GetPosts($postsData);
                if (empty($reels)) {
                    setcookie('watched_reels', json_encode(array()), time()+(60 * 60 * 24),'/');
                    $wo['watched_reels'] = array();
                    $postsData['not_in'] = $wo['watched_reels'];
                    $reels = Wo_GetPosts($postsData);
                    if (empty($reels)) {
                        header("Location: " . $wo['config']['site_url']);
                        exit();
                    }
                }
                $id = $reels[0]['id'];
            }
            if (!empty($_GET['id'])) {
                $id = Wo_Secure($_GET['id']);

                $wo['story'] = Wo_PostData($id);

                if (empty($wo['story'])) {
                    header("Location: " . $wo['config']['site_url']);
                    exit();
                }

                $reels[] = $wo['story'];

                setcookie('watched_reels', json_encode(array()), time()+(60 * 60 * 24),'/');
                $wo['watched_reels'] = array($wo['story']['id']);
                $postsData['not_in'] = $wo['watched_reels'];
                $nextReels = Wo_GetPosts($postsData);
                if (!empty($nextReels)) {
                    $reels = array_merge($reels, $nextReels);
                }
                
            }

            $wo['page_url'] = $wo['config']['site_url']. "/reels/" . $id;
            if (!empty($reelOwnerName)) {
                $wo['page_url'] .= "/".$reelOwnerName;
            }
            $wo['reelOwnerName'] = $reelOwnerName;
            $wo['page'] = 'reels';


            foreach ($reels as $key => $wo['story']) {
                if (!in_array($wo['story']['id'], $wo['watched_reels'])) {
                    $wo['watched_reels'][] = $wo['story']['id'];
                }
                $wo['story']['likeCount'] = Wo_CountLikes($wo['story']['id']);
                $wo['story']['commentCount'] = Wo_CountPostComment($wo['story']['id']);

                $media = array(
                    'type' => 'post',
                    'storyId' => $wo['story']['id'],
                    'filename' => $wo['story']['postFile'],
                    'name' => $wo['story']['postFileName'],
                    'postFileThumb' => $wo['story']['postFileThumb'],
                );
                $video = Wo_DisplaySharedFile($media, '', $wo['story']['cache']);

                $userUrl = '';
                if (!empty($reelOwnerName)) {
                    $userUrl = "/".$reelOwnerName;
                }

                $html .= loadHTMLPage('reels/list',[
                    'ID' => $wo['story']['id'],
                    'URL' => $wo['config']['site_url']. "/reels/" . $wo['story']['id'].$userUrl,
                    'CLASS' => ($main_reel == 0 ? '' : 'hidden'),
                    'STORY_ARRAY' => $wo['story'],
                    'PUBLISHER_ARRAY' => $wo['story']['publisher'],
                    'VIDEO' => $video,
                ]);

                $main_reel = 1;
            }

            $html_sidebar = loadHTMLPage('welcome/reels_list',[
                'html' => $html,
            ]);
        }

        $data = array(
            'status' => 200,
            'tab_name' => $_POST['tab_name'],
            'tab_layout' => $html_sidebar, 
        );
    }

    if ($s == 'explore_change') {
        $wo['page'] = 'welcome';
        if(empty($_POST['explore_type'])) {
            return false;
        }

        $html_sidebar = '';
        if($_POST['explore_type'] == 'products') {
            $data['limit'] = 10;
            $products = Wo_GetProducts($data);
            if (count($products) > 0) {
                foreach ($products as $key => $wo['product']) {
                    $html_sidebar = '<div class="wo_market"><div id="products" class="row">';
                    $html_sidebar .= Wo_LoadPage('welcome/products/product-list');
                    $html_sidebar .= '</div></div>';
                }
			} else {
				echo '<div class="empty_state"><svg enable-background="new 0 0 32 32" height="512" viewBox="0 0 32 32" width="512" xmlns="http://www.w3.org/2000/svg"><path d="m26 32h-20c-3.314 0-6-2.686-6-6v-20c0-3.314 2.686-6 6-6h20c3.314 0 6 2.686 6 6v20c0 3.314-2.686 6-6 6z" fill="#ffe6e2"/><path d="m15.333 17.167c0-.275-.225-.5-.5-.5h-2.5v2c0 .368-.298.667-.667.667-.368 0-.667-.298-.667-.667v-2h-2.499c-.275 0-.5.225-.5.5v6.333c0 .275.225.5.5.5h6.333c.275 0 .5-.225.5-.5z" fill="#fc573b"/><path d="m24 17.167c0-.275-.225-.5-.5-.5h-2.5v2c0 .368-.298.667-.667.667-.368 0-.667-.298-.667-.667v-2h-2.5c-.275 0-.5.225-.5.5v6.333c0 .275.225.5.5.5h6.334c.275 0 .5-.225.5-.5z" fill="#fc573b"/><path d="m19.667 8.5c0-.275-.225-.5-.5-.5h-2.5v2c0 .368-.298.667-.667.667-.368 0-.667-.298-.667-.667v-2h-2.5c-.275 0-.5.225-.5.5v6.333c0 .275.225.5.5.5h6.333c.275 0 .5-.225.5-.5v-6.333z" fill="#fd907e"/></svg>' . $wo['lang']['no_available_products'] . '</div>';
			}
        }else if($_POST['explore_type'] == 'photos') {
            $html_sidebar = Wo_LoadPage('welcome/get_post_photos');
        }else if($_POST['explore_type'] == 'videos') {
            $html_sidebar = Wo_LoadPage('welcome/get_post_videos');
        }

        $data = array(
            'status' => 200,
            'explore_type' => $_POST['explore_type'],
            'explore_html' => $html_sidebar, 
        );
    }

    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
