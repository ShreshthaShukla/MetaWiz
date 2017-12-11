
<?php 
	//including header of the page
	error_reporting();
	include "header.php"; 
?>

	<div>
		<!-- Division to display form to take input for the question-->
		<h2>&nbsp &nbsp Question 2:</h2><br>
		<form method=POST action='question2.php'> <b>
			&nbsp &nbsp &nbsp Which scene is about " <input type="text" name='words'> "?
			<br><br><br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
			<input type="button" value="Swap Question" onclick="window.location.href = 'question1.php'"> &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
			<input type=submit name='submit' value="Submit"></b>
		</form>
	</div>
	
	<div> 
		<!-- Division to retrieve answer for the question and display-->
		<?php
			error_reporting();
			//Display results only if there is an input of words else request for input.
			if ( isset( $_POST['submit'] ) && isset( $_POST['words'] ) && $_POST['words'] != "" ) {
				
				echo '<h2>&nbsp &nbsp Answer:</h2><br>';
				
				//Fetching credential information from the credentials.txt
				$credentials = [];
				$lines = explode( "\n", file_get_contents( 'credentials.txt' ) );
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
				
				//Retrieving scenees where the words are present either in one of the lines spoken by any character or the description
				foreach ( $cursor as $id => $value ) {
					foreach ( $value->contents as $content ) {
						if ( stripos( $content->text, $words ) || stripos( $content->description, $words ) ) {
							echo '<b>Scene :</b><br><br>';
							foreach ( $value->contents as $content ){
								if ( property_exists( $content, 'character' ) )
									echo '<b>'.$content->character.' : </b>';
								if ( property_exists( $content, 'text' ) )
									echo $content->text."<br>";
								if ( property_exists( $content, 'description' ) )
									echo $content->description.'<br>';
							}
							echo '<br><hr><hr>';
							$status = $has_result;
							break;
						}
					}
				}
				//Checking if any match was found else displaying no result
				if ( isset( $status ) AND $status != $has_result ) {
					echo '&nbsp &nbsp &nbsp No results.<br><br>';
				}

			}elseif ( isset( $_POST['submit'] ) AND isset( $_POST['words'] ) ) {
				//Checking for empty input box and showing error.
				echo '&nbsp &nbsp &nbsp Please input the value to search<br><br>';
			}
			
		?>
	</div>

<?php 
	//including footer of the page
	error_reporting();
	include "footer.php"; 
?>