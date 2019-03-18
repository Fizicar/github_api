<?php
/**
 * 
 * Simple Github Search
 * 
 */

if($_GET){
$query_string = str_replace(' ', '+', $_GET['search']);
$sort_by = $_GET['order_by'];
$order = $_GET['order'];

if(isset($_GET['page'])){
   $page = $_GET['page'];
}else{
   $page = 1;
}


function callAPI($method, $url, $data){
    $curl = curl_init();

   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }

   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'User-Agent: Fizicar',
      'Content-Type: application/vnd.github.mercy-preview+json'
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
 }


    $get_data = callAPI('GET', 'https://api.github.com/search/repositories?q='.$query_string.'&sort='.$sort_by.'&page='.$page.'&per_page=15&order='.$order, false);
    $response = json_decode($get_data, true);
    $total_count = $response["total_count"];

    $page_count = ceil($total_count / 15);

    if($page_count > 66){
       $page_count = 67;
    }


    $pagination_urls = paggination_urls($page,$page_count);

}


function paggination_urls($page,$last){
   if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    $link = "https"; 
   else
      $link = "http"; 
   

   $link .= "://"; 
   

   $link .= $_SERVER['HTTP_HOST']; 
   

   $link .= $_SERVER['REQUEST_URI']; 
   
   if (strpos($link, '&page=') === false) {
      $link .= '&page=1';
  }
   

   $next=$page+1;
   $previus=$page*1-1;


   $return_array = array(
      'first' => str_replace('page='.$page*1, 'page=1', $link),
      'next' => str_replace('page='.$page*1, 'page='.$next, $link),
      'previus' => str_replace('page='.$page*1, 'page='.$previus, $link),
      'last' => str_replace('page='.$page*1, 'page='.$last, $link)
   );

   return $return_array;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Git Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<style>

.search_button{
   justify-content: flex-start;
    display: flex;
    flex-flow: column-reverse;   
   
}

</style>
<body>
    <div class="container">
      <div class="row">
         <div class="col-12 text-center">
            <h1>Git Repo Search</h1>
         </div>
         <div class="col-12">
         <form action='' method='get'>
         <div class="form-row">
            <div class="form-group col-md-6">
            <label>Search:</label>
               <input type="text" class="form-control" id="input_search" name='search' placeholder="Search...">
            </div>
            <div class="form-group col-md-2">
            <label>Order by:</label>
               <select id="inputState" class="form-control" name='order_by'>
                  <option value='stars' selected>Stars</option>
                  <option value='forks'>Forks</option>
                  <option value='help-wanted-issues'>Help Wanted Issues</option>
                  <option value='updated'>Last Updated</option>
               </select>
            </div>
            <div class="form-group col-md-2">
            <label>Order: </label>
               <select id="inputState" class="form-control" name='order'>
               <option value='DESC' selected>DESC</option>
               <option value='ASC'>ASC</option>
               </select>
            </div>
            <div class="form-group col-md-2 search_button">
               <button id='search_button' type="submit" class="btn btn-primary">Search</button>
            </div>
         </div>
         
         </form>
         </div>
         <?php 
         if(isset($_GET['search']) && isset($_GET['order_by']) && isset($_GET['order'])){  
         ?>
         <div class="col-12">
         <table class="table">
            <thead>
               <tr>
                  <th scope="col">Repo Name</th>
                  <th scope="col">Repo User</th>
                  <th scope="col">Repo Created</th>
                  <th scope="col">Repo Url</th>
               </tr>
            </thead>
            <tbody>
            <?php foreach ($response['items'] as $key => $value) { ?>
               <tr>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['owner']['login']; ?></td>
                  <td><?php echo date("F d, Y h:i:s A", strtotime($value["created_at"])); ?></td>
                  <td><a href="<?php echo $value["html_url"];?>" target="_blank"><?php echo $value["html_url"];?></a></td>
               </tr>
            <?php }?>
               
            </tbody>
            </table>
         </div>
         <div class="col-12">
               <?php if($page > 1){?>
                  <a href="<?php echo $pagination_urls["first"];?>">First Page</a>
                  <a href="<?php echo $pagination_urls["previus"];?>">Previus Page</a>
               <?php } ?>
               <?php if($page != $page_count){ ?>
               <a href='<?php echo $pagination_urls["next"];?>'>Next Page</a>
               <a href='<?php echo $pagination_urls["last"];?>'>Last Page</a>
               <?php } ?>
               
         </div>
         <?php } ?>
         
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="main.js"></script>
</body>
</html>