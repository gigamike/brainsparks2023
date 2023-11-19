<?php if (count($chatMessages) > 0): ?>
    <ul class="chat">
<?php foreach ($chatMessages as $chatMessage): ?>

    <?php if (is_null($chatMessage->user_id)): ?>
     <li class="left clearfix"><span class="chat-img pull-left">
        <img src="<?php echo $chatMessage->profile_photo; ?>" alt="" class="img-circle" style="width: 50px;" />
    </span>
        <div class="chat-body clearfix">
            <div class="header">
                <strong class="primary-font"><?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?></strong> <small class="pull-right text-muted">
                    <span class="glyphicon glyphicon-time"></span><?php echo time_elapsed($chatMessage->date_added); ?></small>
            </div>
            <p>
                <?php echo str_replace("\n", "<br>", $chatMessage->message); ?>
            </p>
        </div>
    </li>
    <?php else: ?>
    <li class="right clearfix"><span class="chat-img pull-right">
        <img src="<?php echo $chatMessage->profile_photo; ?>" alt="" class="img-circle" style="width: 50px;" />
    </span>
        <div class="chat-body clearfix">
            <div class="header">
                <small class=" text-muted"><span class="glyphicon glyphicon-time"></span><?php echo time_elapsed($chatMessage->date_added); ?></small>
                <strong class="pull-right primary-font"><?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?></strong>
            </div>
            <p>
                <?php echo str_replace("\n", "<br>", $chatMessage->message); ?>
            </p>
        </div>
    </li>
    <?php endif; ?>
<?php endforeach; ?>
    </ul>
<?php endif; ?>