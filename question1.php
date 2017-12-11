<?php 
	//including header of the page
	error_reporting();
	include "header.php"; 
	
?>
	<!-- Division to display form to take input for the question-->
	<div>
		<h2>&nbsp &nbsp Question 1:</h2><br>
		<form method=POST action='question1.php'> <b>
			&nbsp &nbsp &nbsp In which lines does
			<select name='character' id='character'>
				<option value=''>Choose any Character</option>
				<?php 
					//Fetching characters' name list from the characters.txt file to display in the dropdown
					$characters  = explode( "\n", file_get_contents('characters.txt') );
					foreach ( $characters as $character ) {
						$values = explode( ':', $character );
		 				echo "<option value='$values[1]'>$values[0]</option>";
		 			}
				?>
			</select> 
			say " <input type="text" name="words"> "?</b>
			<br><br><br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
			<input type="button" value="Swap Question" onclick="window.location.href = 'question2.php'"> &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
			<input type='submit' name='submit' value="Submit">
		</form>
	</div>
	
	<!-- Division to retrieve answer for the question and display-->
	<div> 
	<?php
		error_reporting();
		//Display results only if there is an input of words else request for input.
		if ( isset($_POST['submit'] ) AND isset( $_POST['words'] ) AND $_POST['words'] != "" ) {
			echo '<h2>&nbsp &nbsp Answer:</h2><br>';
			//Fetching credential information from the credentials.txt
			$credentials = [];
			$lines = explode( "\n", file_get_contents('credentials.txt') );
			foreach ( $lines as $credential ) {
				$type = explode( ':', $credential );
				array_push( $credentials , $type[1] );
			}
			$status = 'no result';
			$has_result = 'has result';
			$words = ' '.$_POST['words'];
			
			//Making connection with MongoDB to fetch the data.
			$url = $credentials[0].'://'.$credentials[1].':'.$credentials[2].'@'.$credentials[3].'/'.$credentials[4];
			$manager = new MongoDB\Driver\Manager( $url );
			$query = new MongoDB\Driver\Query([]);  
			$resource = $credentials[4].'.'.$credentials[5];
			$cursor   = $manager->executeQuery( $resource, $query );
			
			//Retrieving lines where the character name matches and the words are present in the line 
			if ( isset( $_POST['character'] ) AND $_POST['character'] != "" ) {
				$character=$_POST['character'];
				foreach ( $cursor as $id => $value ) {
					foreach ( $value->contents as $content ) {
						if( property_exists( $content, 'character' ) && property_exists( $content, 'text' ) )
						if( ( $content->character == $character ) && stripos( $content->text, $words) ) { 
							echo '<br><b>&nbsp &nbsp'.$content->character.' : </b>';
							echo $content->text;
							echo '<hr>';
							$status = $has_result ;
						}
					}
				}
			}else { //Retrieving lines where the words are present in the line spoken by any character
				foreach ( $cursor as $id => $value ) {
					foreach ( $value->contents as $content ) {
						if( property_exists( $content, 'character' ) && property_exists( $content, 'text' ) )
						if( stripos( $content->text, $words ) ) { 
							echo '<br><b>&nbsp &nbsp'.$content->character.' : </b>';
							echo $content->text;
							echo '<hr>';
							$status = $has_result;
						}
					}
				}
			}
			//Checking if any match was found else displaying no result
			if ( isset( $status ) AND $status != $has_result ) {
				echo '&nbsp &nbsp &nbsp No Results.<br><br>';
			}
		}elseif ( isset( $_POST['submit'] ) AND isset( $_POST['words'] ) AND $_POST['words'] == "" ) {
			//Checking for empty input box and showing error.
			echo '<br><br>&nbsp &nbsp &nbsp Please input the value to search<br><br>';
		}
	 ?>
	</div>

<?php 
	//including footer of the page
	error_reporting();
	include "footer.php"; 	
?>

