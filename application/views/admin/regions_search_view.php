<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Booking Regions',
            'status' => 'active',
            'link' => "/admin/view_regions"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <section>
        <div class="body-typical-body">
          <div class="box-typical box-typical-padding">
              <div class="for-groupss row">
                <div class="col-md-10 columns">
                  <div class="row">
                    <div class="col-md-2">
                      <label class="col-sm-12 form-control-label">&nbsp;</label>
                      <a style="color: #fff;" href="<?php echo base_url(); ?>admin/add_region"><button type="button" class="btn btn-inline">New Region</button></a>
                    </div>
                    <div class="col-md-3">
                      <label class="col-sm-12 form-control-label">&nbsp;</label>
                      <a style="color: #fff;" href="<?php echo base_url(); ?>admin/add_subregion"><button type="button" class="btn btn-inline">New Sub Region</button></a>
                    </div>

                    <!-- TECH FILTER -->
                    <?php
                    $form_attr = array(
                      'id' => 'jform'
                    );
                    echo form_open("admin/search_regions",$form_attr);
                    ?>
                    <div class="col-md-10 columns">
                      <div class="row">
                        <div class="col-md-10">
                          <!--<label for="search">Input Postcode or State</label> -->
                          <label class="col-sm-12 form-control-label">&nbsp;</label>
                          <input type="text" name="postcode" class="form-control" placeholder='Try "4216", "QLD" or "Kingston"' value="" />
                        </div>

                        <div class="col-md-2 columns">
                          <label class="col-sm-12 form-control-label">&nbsp;</label>
                          <button type="submit" class="btn btn-inline">Search</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </form>
              <!-- TECH FILTER END -->

            </div>
            <!-- Accordion -->
            <div id="accordionExample" class="accordion">
              <!-- Accordion item 1 -->
              <?php foreach($state as $item): ?>
              <div class="card">
                <div id="headingOne" class="card-header bg-white shadow-sm border-0 state">
                  <h6 class="mb-0 font-weight-bold"><a href="#" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne" class="d-block position-relative text-dark text-uppercase collapsible-link py-2" style="padding-bottom: 0rem!important;"><?php echo $item->region_state; ?></a></h6>
                </div>
                <?php
                  $region_state = $item->region_state;
                  if($filter_state == 1){
                    $postcode = "";
                    if(!empty($regkeyword)){
                      $data['region'] = $this->admin_model->getSearchRegionsByDistinctRegion($region_state,$regkeyword);
                      //echo $this->db->last_query();
                      //exit();

                    }
                    else if(!empty($subkeyword)){
                      $data['region'] = $this->admin_model->getSearchRegionsByDistinctSubreg($region_state,$subkeyword);
                    }
                    else{
                      $data['region'] = $this->admin_model->getSearchRegionsByDistinctState($region_state,$postcode);
                    }
                  }
                  else{
                    $data['region'] = $this->admin_model->getSearchRegionsByDistinctState($region_state,$postcode);
                  }

                  foreach($data['region'] as $row):
                    $region_name = $row->region_name;
                    $region_id   = $row->region_id;

                    if($filter_state == 1){
                      $postcode = "";
                      if(!empty($subkeyword)){
                        $data['dsubregionname'] = $this->admin_model->getSearchRegionsByDistinctSubkeyword($region_id,$subkeyword);
                        //echo $this->db->last_query();
                        //exit();
                      }
                      else{
                        $data['dsubregionname'] = $this->admin_model->getSearchRegionsByDistinctSubregion($region_id,$postcode);
                        //echo $this->db->last_query();
                        //exit();
                      }
                    }
                    else{
                      $data['dsubregionname'] = $this->admin_model->getSearchRegionsByPostcode($region_id,$postcode);
                    }
                  ?>
                  <div class="container" style="max-width: 1920px !important">
                    <div id="headingOne" class="card-header shadow-sm border-0 region-name">
                      <h6 class="mb-0 font-weight-bold">
                      <a href="#" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne" class="position-relative text-dark text-uppercase collapsible-link py-2"><a href="<?php echo base_url(); ?>admin/edit_region/<?php echo $row->region_id; ?>"><?php echo $region_name; ?></a></h6>
                    </div>
                    <div id="collapseOne" aria-labelledby="headingOne" data-parent="#accordionExample" class="row show">
                    <?php  
                      foreach($data['dsubregionname'] as $key):
                        $subregion_id = $key->sub_region_id;
                        $subregion_name = $key->subregion_name;
                        
                        if($filter_state == 1){
                          $postcode = "";
                          $data['postcodes'] = $this->admin_model->getSeachPostcodeBySubregion($subregion_id,$postcode);
                        }
                        else{
                          $data['postcodes'] = $this->admin_model->getSeachPostcodeBySubregion($subregion_id,$postcode);
                        }
                    ?>
                    <div class="card-body col-md-4" style="float: left;">
                      <p class="subregion-name"><a href="<?php echo base_url(); ?>admin/edit_subregion/<?php echo $region_id; ?>/<?php echo $subregion_id; ?>"><?php echo $key->subregion_name; ?></a></p>
                      <p class="font-weight-light m-0">
                      <?php 
                        $i = 0;
                        $tmp_length = count($data['postcodes']);
                        $length = $tmp_length - 1;
                        foreach($data['postcodes'] as $key):
                          if($i == $length){
                            echo $key->postcode;
                          }
                          else{
                            echo $key->postcode.", ";
                          }
                        $i++;
                        endforeach; 
                      ?>
                      </p>
                    </div>
                  <?php endforeach; ?>
                  </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- End Accordion -->
        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    This page shows the results of your search terms.
    </p>

</div>
<!-- Fancybox END -->

<style type="text/css">
  .card-body {
    -webkit-box-flex: 1;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    padding: 1rem !important;
    padding-top: 0px !important;
  }
  .region-name{
    padding: 0.30rem 0em !important;
  }
  .state{
    padding: 0.50rem 1rem !important;
  }
  p.subregion-name {
      margin-top: 0;
      margin-bottom: 0.20rem !important;
  }
</style>