<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_pinecone_api = get_option('acwc_pinecone_api','');
$acwc_pinecone_environment = get_option('acwc_pinecone_environment','');
$acwc_search_placeholder = get_option('acwc_search_placeholder','Search anything..');
$acwc_search_no_result = get_option('acwc_search_no_result','');
$acwc_search_font_size = get_option('acwc_search_font_size','13');
$acwc_search_font_color = get_option('acwc_search_font_color','#000');
$acwc_search_border_color = get_option('acwc_search_border_color','#ccc');
$acwc_search_bg_color = get_option('acwc_search_bg_color','');
$acwc_search_width = get_option('acwc_search_width','100%');
$acwc_search_height = get_option('acwc_search_height','45px');
$acwc_search_result_font_size = get_option('acwc_search_result_font_size','13');
$acwc_search_result_font_color = get_option('acwc_search_result_font_color','#000');
$acwc_search_result_bg_color = get_option('acwc_search_result_bg_color','');
$acwc_search_loading_color = get_option('acwc_search_loading_color','#ccc');
if(empty($acwc_pinecone_api) || empty($acwc_pinecone_environment)):
    ?>
<p>Seems like you haven't entered your keys, therefore this feature is disabled.</p>
<?php
else:
?>
<style>
    .acwc-search{
        width: <?php echo esc_html($acwc_search_width)?>;
    }
    .acwc-search .acwc-search-form{}
    .acwc-search .acwc-search-form .acwc-search-input{
        height: <?php echo esc_html($acwc_search_height)?>;
        color: <?php echo esc_html($acwc_search_font_color)?>;
        position: relative;
        width: 100%;
        font-size: <?php echo esc_html($acwc_search_font_size)?>px;
    }
    .acwc-search .acwc-search-form .acwc-search-input .acwc-search-field{
        height: <?php echo esc_html($acwc_search_height)?>;
        color: <?php echo esc_html($acwc_search_font_color)?>;
        font-size: <?php echo esc_html($acwc_search_font_size)?>px;
        width: 100%;
        <?php
        if(!empty($acwc_search_bg_color)):
        ?>
        background-color: <?php echo esc_html($acwc_search_bg_color)?>;
        <?php
        endif;
        ?>
        border-color: <?php echo esc_html($acwc_search_border_color)?>;
        border-style: solid;
        border-width: 1px;
        border-radius: 5px;
        box-shadow: none;
    }
    .acwc-search .acwc-search-form .acwc-search-input svg{
        fill: currentColor;
        width: 25px;
        height: 25px;
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: calc(50% - 12.5px);
    }
    .acwc-search-result{
        position: relative;
        min-height: 100px;
        margin-top: 20px;
        <?php
        if(!empty($acwc_search_result_bg_color)):
        ?>
        padding: 10px;
        <?php
        endif;
        ?>
        border-radius: 8px;
        color: <?php echo esc_html($acwc_search_result_font_color)?>;

    }
    .acwc-search-result.acwc-has-item{
    <?php
    if(!empty($acwc_search_result_bg_color)){
    ?>
        background-color: <?php echo esc_html($acwc_search_result_bg_color)?>;
    <?php
    }
    ?>
    }
    .acwc-search-loading{
        display: flex;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        justify-content: center;
        align-items: center;
        <?php
        if(empty($acwc_search_loading_color)):
        ?>
        background: rgb(0 0 0 / 25%);
        <?php
        else:
        ?>
        background: <?php echo esc_html($acwc_search_loading_color)?>;
        <?php
        endif;
        ?>
    }
    .acwc-lds-dual-ring {
        display: inline-block;
        width: 80px;
        height: 80px;
    }
    .acwc-lds-dual-ring:after {
        content: " ";
        display: block;
        width: 64px;
        height: 64px;
        margin: 8px;
        border-radius: 50%;
        border: 6px solid #fff;
        border-color: #fff transparent #fff transparent;
        animation: acwc-lds-dual-ring 1.2s linear infinite;
    }
    @keyframes acwc-lds-dual-ring {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    .acwc-search-item{
        padding-bottom: 20px;
    }
    .acwc-search-item-title{
        font-weight: bold;
        font-size: 20px;
    }
    .acwc-search-item-content{
        font-size: <?php echo esc_html($acwc_search_result_font_size)?>px;
        color: <?php echo esc_html($acwc_search_result_font_color)?>;
    }
    .acwc-search-source{}
    .acwc-search-source h3{
        margin: 10px 0;
    }
    .acwc-search-source a{
        display: inline-block;
        margin-right: 10px;
        color: <?php echo esc_html($acwc_search_result_font_color)?>;
    }
    .acwc-search-item-date{
        font-size: 13px;
        margin-bottom: 5px;
    }
</style>
<div class="acwc-search">
    <form class="acwc-search-form" action="" method="post">

        <div class="acwc-search-input">
            <input autocomplete="off" type="text" name="search" class="acwc-search-field" placeholder="<?php echo esc_attr($acwc_search_placeholder)?>">
            <svg class="acwc-search-submit" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>
        </div>
    </form>
    <div class="acwc-search-result">

    </div>
    <div class="acwc-search-source"></div>
</div>
<script>
    var acwc_nonce = '<?php echo esc_html(wp_create_nonce( 'acwc-chatbox' ))?>';
    var acwcSearch = document.getElementsByClassName('acwc-search')[0];
    var acwcSearchForm = acwcSearch.getElementsByClassName('acwc-search-form')[0];
    var acwcSearchField = acwcSearch.getElementsByClassName('acwc-search-field')[0];
    var acwcSearchResult = acwcSearch.getElementsByClassName('acwc-search-result')[0];
    var acwcSearchSource = acwcSearch.getElementsByClassName('acwc-search-source')[0];
    var acwcSearchBtn = acwcSearch.getElementsByClassName('acwc-search-submit')[0];
    acwcSearchBtn.addEventListener('click', function (){
        acwcSearchData();
    });
    function acwcExpand(el){
        var acwcSearchItem = el.closest('.acwc-search-item');
        acwcSearchItem.getElementsByClassName('acwc-search-item-excerpt')[0].style.display = 'none';
        acwcSearchItem.getElementsByClassName('acwc-search-item-full')[0].style.display = 'block';
    }
    function acwcSearchData(){
        var search = acwcSearchField.value;
        if(search !== '') {
            acwcSearchResult.innerHTML = '<div class="acwc-search-loading"><div class="acwc-lds-dual-ring"></div></div>';
            acwcSearchSource.innerHTML = '';
            acwcSearchResult.classList.remove('acwc-has-item');
            const xhttp = new XMLHttpRequest();
            xhttp.open('POST', '<?php echo admin_url('admin-ajax.php')?>');
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send('action=acwc_search_data&_wpnonce='+acwc_nonce+'&search='+encodeURIComponent(search));
            xhttp.onreadystatechange = function(oEvent) {
                if (xhttp.readyState === 4) {
                    if (xhttp.status === 200) {
                        acwcSearchResult.classList.add('acwc-has-item');
                        var acwc_response = this.responseText;
                        if (acwc_response !== '') {
                            acwc_response = JSON.parse(acwc_response);
                            acwcSearchResult.innerHTML = '';
                            if (acwc_response.status === 'success') {
                                if(acwc_response.data.length){
                                    for(var i = 0; i < acwc_response.data.length; i++){
                                        var item = acwc_response.data[i];
                                        acwcSearchResult.innerHTML += item;
                                    }
                                    if(acwc_response.source.length){
                                        acwcSearchSource.innerHTML = '<h3><?php echo esc_html(__('Sources:','gpt3-ai-content-generator'))?></h3>';
                                        for(var i = 0; i < acwc_response.source.length; i++){
                                            var item = acwc_response.source[i];
                                            acwcSearchSource.innerHTML += item;
                                        }
                                    }
                                }
                                else{
                                    acwcSearchResult.innerHTML = '<p><?php echo esc_html(__('No result found','gpt3-ai-content-generator'))?></p>';
                                }
                            }
                            else{
                                acwcSearchResult.innerHTML = '<p class="acwc-search-error">'+acwc_response.msg+'</p>';
                            }
                        }
                        else{
                            acwcSearchResult.innerHTML = '<p class="acwc-search-error">Something went wrong</p>';
                        }
                    }
                    else{
                        acwcSearchResult.innerHTML = '<p class="acwc-search-error">Something went wrong</p>';
                    }
                }
            }

        }
    }
    acwcSearchForm.addEventListener('submit', function (e){
        acwcSearchData();
        e.preventDefault();
        return false;
    })
</script>
<?php
endif;
