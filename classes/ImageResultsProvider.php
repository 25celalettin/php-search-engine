<?php
class ImageResultsProvider {
   private $con;

   public function __construct ($con) {
      $this->con = $con;
   }

   public function getNumResults($term) {
      $query = $this->con->prepare("SELECT COUNT(*) as total FROM images WHERE (title LIKE :term OR alt LIKE :term) AND broken=0");
      $searchTerm = "%" . $term . "%";
      $query->bindParam(":term", $searchTerm);
      $query->execute();

      $row=$query->fetch(PDO::FETCH_ASSOC);
      return $row['total'];
   }

   public function getResultsHtml($page, $pagesize, $term) {
      $fromLimit = ($page - 1) * $pagesize;

      $query = $this->con->prepare("SELECT * FROM images WHERE (title LIKE :term OR alt LIKE :term) AND broken=0 ORDER BY clicks DESC LIMIT :fromLimit, :pagesize");
      $searchTerm = "%" . $term . "%";
      $query->bindParam(":term", $searchTerm);
      $query->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT);
      $query->bindParam(":pagesize", $pagesize, PDO::PARAM_INT);
      $query->execute();

      $resultsHtml = "<div class='imageResults'>";

      $count = 0;
      while ($row=$query->fetch(PDO::FETCH_ASSOC)) {
         $count++;
         $id = $row['id'];
         $imageUrl = $row['imageUrl'];
         $siteUrl = $row['siteUrl'];
         $alt = $row['alt'];
         $title = $row['title'];

         if ($title) {
            $displayText = $title;
         } elseif ($alt) {
            $displayText = $alt;
         } else {
            $displayText = $imageUrl;
         }

         $resultsHtml .= "<div class='gridItem image$count'>
                           <a href='$imageUrl' data-fancybox> <img src='$imageUrl'> </a>
                           
                           <script>

                           $(document).ready(function() {
                              loadImage(\"$imageUrl\", \"image$count\");
                           });

                           </script>

                           <span class='details'> $displayText </span>
                         </div>";

      }

      $resultsHtml .= "</div>"; 
      return $resultsHtml;
   }



}

?>