<?php

// Set timeout
set_time_limit(10000);

// Inculde thư viện PHPCrawl
include("libs/PHPCrawler.class.php");
include("simple_html_dom.php");


// Extend the class PHPCrawler and cài đè phương thức handleDocumentInfo()
class MyCrawler extends PHPCrawler 
{
  // Các bạn cài đè phương thức handleDocumentInfo() để xử lý tất cả các thông tin thu tập được.
  function handleDocumentInfo(PHPCrawlerDocumentInfo $DocInfo) 
  {

  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "baby";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  } 
  mysqli_set_charset($conn,"utf8");
    // Lấy toàn bộ url của website
    echo "Page requested: ".$DocInfo->url."</br>";
    
  // lấy file html từ các links crawler được.
  $html = file_get_html($DocInfo->url);

  if(is_object($html)){
    
      $tagA = $html->find('div[class=product_title] a[class=thumb]');
      if($tagA){
        
        foreach ($tagA as $A) {
          $link = $A->href;

          $product = file_get_html($link);
          $name = $product->find('h1',0);
          $img  = $product->find('.etalage_thumb_image',0);
          $price  = $product->find('.price',0);
          $intro  = $product->find('.attribute-info',0);

          $name = $name->innertext;
          $img = $img->src;
          $price = (integer)$price->innertext;
          $price = $price*1000;
          $intro = $intro->plaintext;

          // echo $name.'<br>';
          // echo $img.'<br>';
          // echo $price.'<br>';
          // echo $intro.'<br>';

          // echo $name->innertext.'<br>';
          // echo $img->src.'<br>';
          // echo $price->innertext.'<br>';
          // echo $intro->innertext.'<br>';

          $sql = "INSERT INTO products (name, intro, images, price, quantity, category_id)
          VALUES ('$name', '$intro', '$img','$price','100','29')";

          if ($conn->query($sql) === TRUE) {
              echo "New record created successfully <br>";
          } else {
              echo "Error: " . $sql . "<br>" . $conn->error;
          }
        }
      }

      $html->clear(); 
      unset($html);
  }
  
    flush();
  } 
}

// Tạo đối tượng crawler và bắt đầu tiến trình thu thập dữ liệu

$crawler = new MyCrawler();

// set URL mà ta muốn crawler
$crawler->setURL("https://bibomart.com.vn/be-tam-c317.html");

// Chỉ lấy các file mà nội dung là "text/html"
$crawler->addContentTypeReceiveRule("#text/html#");

// Một bộ lọc cho phép ta không lấy các link ảnh, css hoặc javascript
$crawler->addURLFilterRule("#(jpg|gif|png|pdf|jpeg|svg|css|js)$# i");

// Trong quá trình crawler, lưu trữ và gửi cookie giống như ta vào bằng trinh duyệt
$crawler->enableCookieHandling(true);

// Thiết lập dung lượng(bytes) thu thập được trong quá trình crawler
$crawler->setTrafficLimit(1000 * 1024);

// Nào, chạy thôi, hehe, :))
$crawler->go();

// Sau khi quá trình crawler kết thúc, ghi lại báo cáo!!

$report = $crawler->getProcessReport();

if (PHP_SAPI == "cli") $lb = "\n";
else $lb = "<br />";
    
echo "Summary:".$lb;
echo "Links followed: ".$report->links_followed.$lb;
echo "Documents received: ".$report->files_received.$lb;
echo "Bytes received: ".$report->bytes_received." bytes".$lb;
echo "Process runtime: ".$report->process_runtime." sec".$lb; 
?>