fetch('php/get_user_data.php')
    .then(response => {

        if (!response.ok) {
            throw new Error('Failed to fetch user data.');
        }
        return response.json();
    })
    .then(data => {

        document.getElementById('user-name').textContent = data.username;

        document.getElementById('user-name-detail').textContent = data.username;

        document.getElementById('user-email').textContent = data.email;
    })
    .catch(error => {
        console.error('Error fetching user data:', error); 
        document.getElementById('user-name').textContent = "Guest";
        document.getElementById('user-email').textContent = "Not available";
    });
