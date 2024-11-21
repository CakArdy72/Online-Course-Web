document.querySelector('#add-user-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form reload halaman

    // Ambil nilai input
    const username = document.querySelector('#username').value.trim();
    const email = document.querySelector('#email').value.trim();
    const password = document.querySelector('#password').value.trim();
    const role = document.querySelector('#role').value; // Ambil role dari dropdown

    // Validasi input
    if (!username || !email || !password || !role) {
        alert('Semua kolom wajib diisi!');
        return;
    }

    if (!validateEmail(email)) {
        alert('Format email tidak valid!');
        return;
    }

    if (password.length < 6) {
        alert('Password harus minimal 6 karakter!');
        return;
    }

    // Kirim data ke backend menggunakan fetch
    fetch('http://localhost/restapi/restapi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: username, email, password, role }),
    })
    .then(response => {
        console.log('Status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response Data:', data);
        if (data.message === "User created successfully") {
            alert('User berhasil ditambahkan!');
            document.querySelector('#add-user-form').reset(); // Reset form
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan, coba lagi!');
    });
    
});

// Fungsi validasi email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
