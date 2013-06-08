
<label for="<?php echo $this->get_field_id('title'); ?>">Title:
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label>
<label for="<?php echo $this->get_field_id('numResults'); ?>">Number of activities to show:
<select class="widefat" style="width: 100;" name="<?php echo $this->get_field_name('numResults'); ?>">
    <option value="1"<?php echo attribute_escape($numResults) == '1' ? ' selected="selected"' : ''; ?>>1</option>
    <option value="2"<?php echo attribute_escape($numResults) == '2' ? ' selected="selected"' : ''; ?>>2</option>
    <option value="3"<?php echo attribute_escape($numResults) == '3' ? ' selected="selected"' : ''; ?>>3</option>
    <option value="4"<?php echo attribute_escape($numResults) == '4' ? ' selected="selected"' : ''; ?>>4</option>
    <option value="5"<?php echo attribute_escape($numResults) == '5' ? ' selected="selected"' : ''; ?>>5</option>
    <option value="6"<?php echo attribute_escape($numResults) == '6' ? ' selected="selected"' : ''; ?>>6</option>
    <option value="7"<?php echo attribute_escape($numResults) == '7' ? ' selected="selected"' : ''; ?>>7</option>
    <option value="8"<?php echo attribute_escape($numResults) == '8' ? ' selected="selected"' : ''; ?>>8</option>
    <option value="9"<?php echo attribute_escape($numResults) == '9' ? ' selected="selected"' : ''; ?>>9</option>
</select><br />
<label for="<?php echo $this->get_field_id('widgetStyle'); ?>">Widget Style (View examples <a href="/widget-styles/">here</a>):
<select class="widefat" style="width: 100;" name="<?php echo $this->get_field_name('widgetStyle'); ?>">
    <option value="1"<?php echo attribute_escape($widgetStyle) == '1' ? ' selected="selected"' : ''; ?>>Style 1</option>
    <option value="2"<?php echo attribute_escape($widgetStyle) == '2' ? ' selected="selected"' : ''; ?>>Style 2</option>
    <option value="3"<?php echo attribute_escape($widgetStyle) == '3' ? ' selected="selected"' : ''; ?>>Style 3</option>
</select><br />
<BR/><BR/>

<label>Data to show on the widget:<BR/><BR/>
<input class="" id="<?php echo $this->get_field_id('displayName'); ?>" name="<?php echo $this->get_field_name('displayName'); ?>" type="checkbox" value="displayName" <?php echo attribute_escape($activity_name) == 'displayName' ? ' checked' : ''; ?>/> Activity Name (set in GC)</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayActivityType'); ?>" name="<?php echo $this->get_field_name('displayActivityType'); ?>" type="checkbox" value="displayActivityType" <?php echo attribute_escape($activity_type) == 'displayActivityType' ? ' checked' : ''; ?>/> Activity Type (Run/Bike/etc)</label><BR/>

<input class="" id="<?php echo $this->get_field_id('displayDate'); ?>" name="<?php echo $this->get_field_name('displayDate'); ?>" type="radio" value="short" <?php echo attribute_escape($date) == 'short' ? ' checked' : ''; ?>/> Short Date (ex: <?PHP echo date(get_option("date_format"));?>)</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayDate'); ?>" name="<?php echo $this->get_field_name('displayDate'); ?>" type="radio" value="long" <?php echo attribute_escape($date) == 'long' ? ' checked' : ''; ?>/> Long Date<BR/> (ex:<?PHP echo date(get_option("date_format") . " " . get_option("time_format"));?>)</label><BR/>


<input class="" id="<?php echo $this->get_field_id('displayDuration'); ?>" name="<?php echo $this->get_field_name('displayDuration'); ?>" type="checkbox" value="displayDuration" <?php echo attribute_escape($duration) == 'displayDuration' ? ' checked' : ''; ?>/> Duration (ex: 30:12 min run)</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayDistance'); ?>" name="<?php echo $this->get_field_name('displayDistance'); ?>" type="checkbox" value="displayDistance" <?php echo attribute_escape($distance) == 'displayDistance' ? ' checked' : ''; ?>/> Distance</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displaySpeed'); ?>" name="<?php echo $this->get_field_name('displaySpeed'); ?>" type="checkbox" value="displaySpeed" <?php echo attribute_escape($speed) == 'displaySpeed' ? ' checked' : ''; ?>/> Speed</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayHR'); ?>" name="<?php echo $this->get_field_name('displayHR'); ?>" type="checkbox" value="displayHR" <?php echo attribute_escape($hr) == 'displayHR' ? ' checked' : ''; ?>/> Heart Rate</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayEnergy'); ?>" name="<?php echo $this->get_field_name('displayEnergy'); ?>" type="checkbox" value="displayEnergy" <?php echo attribute_escape($energy) == 'displayEnergy' ? ' checked' : ''; ?>/> Calories Burned</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayCadence'); ?>" name="<?php echo $this->get_field_name('displayCadence'); ?>" type="checkbox" value="displayCadence" <?php echo attribute_escape($cadence) == 'displayCadence' ? ' checked' : ''; ?>/> Cadence</label><BR/>
<!--<input class="widefat" id="<?php echo $this->get_field_id('displayEventType'); ?>" name="<?php echo $this->get_field_name('displayEventType'); ?>" type="checkbox" value="displayEventType" <?php echo attribute_escape($event_type) == 'displayEventType' ? ' checked' : ''; ?>/> Event Type (Race/Training/etc)</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayCoordinates'); ?>" name="<?php echo $this->get_field_name('displayCoordinates'); ?>" type="checkbox" value="displayCoordinates" <?php echo attribute_escape($coordinates) == 'displayCoordinates' ? ' checked' : ''; ?>/> GPS Coordinates</label><BR/>
<input class="" id="<?php echo $this->get_field_id('displayMap'); ?>" name="<?php echo $this->get_field_name('displayMap'); ?>" type="checkbox" value="displayMap" <?php echo attribute_escape($map) == 'displayMap' ? ' checked' : ''; ?>/> Map</label><BR/>-->