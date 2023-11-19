<div class="row m-t-lg">
    <div class="col-lg-3">
        <div class="form-group">
            <label>1.1 Child’s Age <span class="text-danger">*</span></label>
            <select name="1_1" class="form-control input-lg">
                <?php for ($ctr = 1; $ctr <=10; $ctr++): ?>
                    <option value="<?php echo $ctr; ?>"><?php echo ($ctr) == 1 ? "$ctr year old" : "$ctr years old"; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label>1.2 Child’s Gender <span class="text-danger">*</span></label>
            <div class="radio">
              <label>
                <input type="radio" name="1_2" value="boy" checked>
                Boy
              </label>
            </div>
            <div class="radio">
              <label>
                <input type="radio" name="1_2" value="girl">
                Girl
              </label>
            </div>
        </div>

    </div>
</div>