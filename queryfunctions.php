<?php 
  include "db_connect.php"; 

function getUserBioByUsername($username, $conn) {
        // Define the SQL query
        $sql = "SELECT bio 
                FROM profile  
                WHERE username = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("s", $username);  // "s" specifies the type (string)
            
            // Execute the query
            $stmt->execute();
            
            // Bind the result variables
            $stmt->bind_result($storedBio);
    
            // Check if any row was returned
            if ($stmt->fetch()) {
                // Return the bio
                return $storedBio;
            } else {
                // If no bio found for the user, return an appropriate message
                return "No bio found for this user.";
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // If the statement failed to prepare, return an error message
            return "Error preparing statement.";
        }
    }

    function getUserEmailByUsername($username, $conn) {
        // Define the SQL query
        $sql = "SELECT email 
                FROM userInfo  
                WHERE username = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("s", $username);  // "s" specifies the type (string)
            
            // Execute the query
            $stmt->execute();
            
            // Bind the result variables
            $stmt->bind_result($storedEmail);
    
            // Check if any row was returned
            if ($stmt->fetch()) {
                // Return the bio
                return $storedEmail;
            } else {
                // If no bio found for the user, return an appropriate message
                return "No bio found for this user.";
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // If the statement failed to prepare, return an error message
            return "Error preparing statement.";
        }
    }

function getUserTagsByUsername($username, $conn) {
    $sql = "SELECT tags.name 
        FROM profile_tags JOIN tags 
        ON profile_tags.id = tags.id 
        WHERE username = ?"; 
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("s", $username);  // "s" specifies the type (string)
        
        // Execute the query
        $stmt->execute();
        
        // Get the result set
        $result = $stmt->get_result();
        
        // Initialize an array to store tags
        $tags = [];
        
        // Check if any tags are returned
        if ($result->num_rows > 0) {
            // Fetch tags and store them in the $tags array
            while ($row = $result->fetch_assoc()) {
                $tags[] = $row['name'];  // Add the tag to the array
            }
        } else {
            // If no tags are found, return a default message in the array
            $tags[] = "No tags yet selected";
        }
        
        // Close the statement
        $stmt->close();
        
        // Return the list of tags
        return $tags;
    } else {
        // If the query failed to prepare
        return ["Error preparing the statement."];
    }
}
 