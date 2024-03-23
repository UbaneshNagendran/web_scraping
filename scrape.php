<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get submitted URL and sanitize it
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);

    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        die("Invalid URL provided.");
    }

    // Perform scraping (replace this with your scraping logic)
    $htmlContent = file_get_contents($url);

    // Check if scraping was successful
    if ($htmlContent === false) {
        die("Failed to retrieve content from the provided URL.");
    }

    // Extract headlines from HTML content
    $doc = new DOMDocument();
    @$doc->loadHTML($htmlContent);

   // Extract headlines from HTML content
$headlineTags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'); // Add more header tags as needed

$headlines = array();
foreach ($headlineTags as $tag) {
    $headlineElements = $doc->getElementsByTagName($tag);
    foreach ($headlineElements as $element) {
        $headlines[] = $element->nodeValue;
    }
}


    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "web_scrap";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute SQL query to insert scraped headlines into database
    $sql = "INSERT INTO scraped_websites (url, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Insert each headline into the database
    foreach ($headlines as $headline) {
        $stmt->bind_param("ss", $url, $headline);
        if ($stmt->execute()) {
            echo "Headline '$headline' scraped and saved successfully.<br>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <!-- Styled URL input box -->
    <input type="url" name="url" placeholder="Enter URL here" required>
    <br>
    <button type="submit">Scrape Headlines</button>
</form>

</body>
</html>
