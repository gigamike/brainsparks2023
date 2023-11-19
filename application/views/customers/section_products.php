<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php $rows = count($products); ?>
    <?php if($rows > 0): ?>
        <?php $ctr = 0; ?>
        <?php $ctr2 = 0; ?>
        
        <?php foreach ($products as $product) : ?>
            <?php $ctr++; ?>
            <?php $ctr2++; ?>
            
            <?php if($ctr2 == 1): ?>
            <div class="row">
            <?php endif; ?>
            
            <div class="col-md-4">
                <div class="ibox">
                    <div class="ibox-content product-box">
                        <div class="product-imitation">
                            <img class="img-responsive" src="<?php echo $product->photo; ?>">
                        </div>
                        <div class="product-desc">
                            <span class="product-price">
                                $<?php echo $product->price; ?>
                            </span>
                            <a href="#" class="product-name"> <?php echo $product->name; ?></a>

                            <!-- <div class="small m-t-xs">
                                <?php echo $product->description; ?>
                            </div> -->
                            <div class="m-t text-righ">

                                <a href="#" class="btn btn-xs btn-outline btn-primary">Info <i class="fa fa-long-arrow-right"></i> </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($ctr2%3 == 0): ?>
            <?php $ctr2 = 0; ?>
            </div>
            <hr>
            <?php endif; ?>
            
            <?php if($ctr == $rows): ?>
                <?php if($ctr2%3 != 0): ?>
                    <div class="col-md-4"></div> 
                    </div>
                <?php endif; ?>
            <?php endif; ?>
  
            
        <?php endforeach; ?>
    <?php endif; ?>