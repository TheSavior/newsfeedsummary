
  <div id="results" class="container">
    <div class="row">
      <!-- Images -->
      <div class="span8">
        <div class="images">
          <?php
            $photos = $this->Types['photos'];
            $photo = array_shift($photos);
            //die(var_dump($photo["original"]->attachment->media[0]->photo->fbid));
          ?>
          <a href="<?= $photo['original']->attachment->media[0]->href?>">
            <div class="bannerImage">
              <div class="banneroverflowwrapper">
                <img src="<?= $photo["original"]->picture?>" class="largepic" alt="" />
              </div>
              <div class="bottomImage">
                <div class="caption shiddy">
                  <?php if(property_exists($photo['original'], "message")): ?>
                    <span><?= \Application\Classes\Utils::dotdotdot($photo['original']->message, 65) ?></span>
                  <?php endif; ?>
                </div>
                <div class="statusnums">
                  <span class="likes shiddy"><?= $photo['original']->likes->count ?></span>
                    <span class="comments shiddy"><?= $photo['original']->comment_info->comment_count ?></span>
                </div>
              </div>
            </div>
          </a>

          <div class="smallImages">
            <?php
            foreach ($photos as $photo) { ?>
            <a href="<?= $photo['original']->attachment->media[0]->href?>">
              <div class="thumbwrapper">
                <div class="thumboverflowwrapper">
                  <img src="<?= $photo["original"]->picture?>" class="thumb" />
                </div>
                <div class="bottomImage">
                  <div class="statusnums">
                    <span class="likes shiddy"><?= $photo['original']->likes->count ?></span>
                    <span class="comments shiddy"><?= $photo['original']->comment_info->comment_count ?></span>
                  </div>
                </div>
              </div>
            </a>
            <?php
            }
            ?>
          </div>

        </div>
      </div>

      <!-- Status feeds -->
      <div class="span4">
          <div class="statuses">
          <?php
            foreach ($this->Types['status'] as $status) {
                //die(var_dump($status["original"]));
                $userId = \Application\Classes\Utils::convertId($status["original"]->actor_id);
            ?>

              <div class="status">
            <div class="statusContent">
                <div class="statusTop">
                  <div class="avatar" data-toggle="tooltip" title="<?=$userId?>">
                    <a href="http://facebook.com/<?=$userId?>">
                      <img src="http://graph.facebook.com/<?=$userId?>/picture" />
                    </a>
                  </div>
                  <a href="http://facebook.com/<?=$status["original"]->post_id?>">
                    <p><?=$status["original"]->message ?></p>
                  </a>
                </div>
              <div class="statusbottom">
                <div class="datestamp pull-left">
                  <span><?= \Application\Classes\Utils::formatDate($status['original']->created_time) ?></span>
                </div>
                <div class="statusnums">
                    <span class="likes"><?= $status["original"]->likes->count ?></span>
                    <span class="comments"><?= $status['original']->comment_info->comment_count ?></span>
                </div>
              </div>
            </div>
          </div>
                <?php
            }
          ?>
        </div>
      </div>
    </div>
    <div class="row">
    </div>
  </div>