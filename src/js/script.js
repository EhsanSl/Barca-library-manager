document.addEventListener('DOMContentLoaded', function() {
    // console.log('User Title from js:', userTitle);

    var modal = document.getElementById("borrowModal");
    var span = document.getElementsByClassName("close")[0];
    var cancelBorrow = document.getElementById("cancelBorrow");
    
    // Assuming userTitle is globally available. 
    // It should be set in the HTML by PHP based on the user's title (student or teacher)

    window.openModal = function(bookTitle, isbn) {
        console.log('User Title inside openModal:', userTitle); // Debug line
        document.getElementById("modalTitle").innerText = bookTitle;
        document.getElementById("modalTitle").setAttribute("data-isbn", isbn);
        document.getElementById("confirmBorrow").setAttribute('data-isbn', isbn);
    
        // Calculate the return date based on user title
        var returnDate = new Date();
        if (userTitle === 'STUDENT') {
            returnDate.setMonth(returnDate.getMonth() + 1);
        } else if (userTitle === 'TEACHER') {
            returnDate.setMonth(returnDate.getMonth() + 3);
        }
        document.getElementById("returnDate").innerText = returnDate.toDateString();
    
        modal.style.display = "block";
    };

    span.onclick = cancelBorrow.onclick = function() {
        modal.style.display = "none";
    };

    function borrowBook(isbn) {
        fetch('handle_borrow_book.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
            body: 'isbn=' + encodeURIComponent(isbn) + '&borrower_email=' + encodeURIComponent(borrowerEmail)
        })
        .then(response => response.text())
        .then(data => {
            if (data === "success") {
                alert("Book borrowed successfully!");
                modal.style.display = "none";
                window.location.reload();
            } else {
                alert("Error borrowing book: " + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    }

    document.getElementById("confirmBorrow").addEventListener("click", function() {
        var isbn = this.getAttribute('data-isbn');
        borrowBook(isbn);
    });

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    window.check_all = function(source) {
        var checkboxes = document.getElementsByName('delete_check[]');
        for(var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    };
});
