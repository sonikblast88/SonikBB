<?php
require_once 'core/autoload.php';

// Load required files
require_once 'models/Users.php';
require_once 'models/Topics.php';
require_once 'template/header.php';

class Search {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function searchResults($query) {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }
        
        $stmt = $this->db->prepare("SELECT topic_id, topic_name FROM topics WHERE topic_name LIKE ? OR topic_desc LIKE ?");
        $searchTerm = "%" . $query . "%";
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize database
$database = new Database();
$db = $database->connect();
$topicsModel = new Topics($db);


$search = new Search($db);
$results = [];
$query = isset($_GET['q']) ? $_GET['q'] : '';

if (!empty($query)) {
    $results = $search->searchResults($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
</head>
<body>
<div id="content">
    <div id="topic">
		<h2>Search</h2>
		<form action="search.php" method="GET">
			<input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search topics...">
			<button type="submit">Search</button>
		</form>
		
		<?php if (!empty($query)): ?>
			<h3>Results:</h3>
			<ul>
				<?php foreach ($results as $topic): ?>
					<li><a href="topic.php?topic_id=<?php echo $topic['topic_id']; ?>"><?php echo htmlspecialchars($topic['topic_name']); ?></a></li>
				<?php endforeach; ?>
			</ul>
			
			<?php if (empty($results)): ?>
				<p>No results found.</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
</body>
</html>

<?php 
include_once('aside.php');
include_once 'template/footer.php';
?>