<?php
namespace Application\Classes;

class DataGrabber
{
    private $service;

    public function __construct($service){
        $this->service = $service;
    }

    function getData($start, $end) {
        $query = "/fql?q=".urlencode(
"SELECT type, post_id, attachment, actor_id, target_id, message, comment_info, likes, share_count, created_time
FROM stream WHERE
filter_key IN (SELECT filter_key FROM stream_filter WHERE uid = me() AND type = 'newsfeed')
AND NOT (actor_id IN
    (SELECT target_id FROM connection WHERE target_type='Page' AND source_id = me())
)
AND type IN (46, 56, 80, 257)
AND created_time <= ".$end." AND created_time >= ".$start." LIMIT 500");

        $results = json_decode($this->service->request($query))->data;

        return $results;
    }

    public function run($data) {

        $stories = 0;

        $types = array("photos" => array(), "statuses" => array());
        $storyArray = array();


        foreach($data as $ele) {
            if (!property_exists($ele->likes, "count")) {
                // For some reason, no like count is given
                $ele->likes->count = 0;
            }

            $score = $ele->likes->count + 0.2* $ele->comment_info->comment_count + 2* $ele->share_count;

            $item = array("score" => $score, "original" => $ele);

            if (property_exists($ele->attachment, "media") && isset($ele->attachment->media[0]) && property_exists($ele->attachment->media[0], "photo")) {
                // it's a photo
                $types["photos"][] = $item;
            }
            else if (in_array($ele->type, array(46, 80)) && $ele->message != "")
            {
                $types["statuses"][] = $item;
            }
            else
            {
                //$this->p_print($ele, true);
            }

            $stories++;
        }

        //echo count($types["photos"])." photos, ".count($types["statuses"])." statuses, ".$stories." total stories";

        $newStories = array(
            "photos" => $this->fixPictures($this->getImportant($types["photos"])),
            "status" => $this->getImportant($types["statuses"])
        );

        return $newStories;
    }

    function getImportant($stories) {
        // Get rid of the ones with a score of zero
        $popStories = array_filter($stories, function($item) {
            return $item["score"] > 0;
        });

        // Sort the ones left by their score
        usort($popStories, function ($a, $b) {
            return $a["score"] < $b["score"];
        });

        // If there are no stories, short circuit
        if (count($popStories) == 0) {
            return array();
        }

        // Get the average score for stories in this category
        $avg = array_reduce($popStories, function($acc, $item) {
            return $acc + $item["score"];
        }) / count($popStories);

        // For each element, get the distance from the average score
        $distFromAvg = array_map(function($item) use ($avg) {
            $dist = round(pow($item["score"] - $avg,2));
            return $dist;
        }, $popStories);

        $stdDev = sqrt(array_sum($distFromAvg) / count($popStories));

        // Get all the items that are a standard deviation above the average score
        $results = array_filter($popStories, function($item) use ($avg, $stdDev){
            return $item["score"] > $avg + ($stdDev * .5);
        });

        return $results;
    }

    function fixPictures($photos) {
        foreach($photos as $photo) {

            try
            {
                $photoId = $photo["original"]->attachment->media[0]->photo->fbid;
                $photoId = sprintf('%0.0f',$photoId);
                $request = json_decode($this->service->request("/".$photoId))->images;
                //die(var_dump($request));
                $chosenPhoto = $request[0];
                $photo["original"]->picture = $chosenPhoto->source;
            }
            catch(\Exception $e) {
                $photo["original"]->picture = $photo["original"]->attachment->media[0]->src;
                continue;
            }
        }

        return $photos;
    }

    function p_print($var, $die=false) {
        $s = "<pre>".var_export($var, true)."</pre>";
        if ($die) {
            die($s);
        }
        else echo $s."<br />\n";
    }
}
