document.addEventListener('DOMContentLoaded', function() {
    // Set Bangla font
    document.body.style.fontFamily = 'Bangla, Arial, sans-serif';
    
    // Global variable to store current SMS data
    let currentSmsData = null;
    
    // Calculate due amount automatically
    const calculateDue = function() {
        const total = parseFloat(document.getElementById('total').value) || 0;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        document.getElementById('due').value = (total - paid).toFixed(2);
    };
    
    // Set up event listeners for calculation
    document.getElementById('total').addEventListener('input', calculateDue);
    document.getElementById('paid').addEventListener('input', calculateDue);
    
    // Edit form calculation
    const editCalculateDue = function() {
        const total = parseFloat(document.getElementById('editTotal').value) || 0;
        const paid = parseFloat(document.getElementById('editPaid').value) || 0;
        document.getElementById('editDue').value = (total - paid).toFixed(2);
    };
    
    document.getElementById('editTotal').addEventListener('input', editCalculateDue);
    document.getElementById('editPaid').addEventListener('input', editCalculateDue);
    
    // Edit functionality
    window.openEditModal = function(id, mobile, name, products, total, paid, due, date) {
        document.getElementById('editId').value = id;
        document.getElementById('editMobile').value = mobile || '';
        document.getElementById('editName').value = name || '';
        document.getElementById('editProducts').value = products || '';
        document.getElementById('editTotal').value = total || '';
        document.getElementById('editPaid').value = paid || '';
        document.getElementById('editDue').value = due || '';
        document.getElementById('editDate').value = date || '';
        
        document.getElementById('editModal').style.display = 'flex';
    };
    
    // Close modals
    const closeModals = function() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.getElementById('manualSmsContainer').style.display = 'none';
        document.getElementById('smsResult').style.display = 'none';
    };
    
    document.querySelectorAll('.close-modal, .close-edit-modal, .cancel-btn').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModals();
        }
    });
    
    // Delete functionality
    window.deleteEntry = function(id) {
        if (confirm('আপনি কি নিশ্চিতভাবে এই এন্ট্রি ডিলিট করতে চান?')) {
            window.location.href = `process.php?delete=${id}`;
        }
    };
    
    // Prepare SMS data
    window.prepareSms = function(mobile, name, products, total, paid, due, date) {
        currentSmsData = {
            mobile: mobile,
            name: name,
            products: products,
            total: total,
            paid: paid,
            due: due,
            date: date
        };
        
        document.getElementById('smsModal').style.display = 'flex';
        document.getElementById('manualSmsContainer').style.display = 'none';
        document.getElementById('smsResult').style.display = 'none';
    };
    
    // Auto SMS button
    document.getElementById('autoSms').addEventListener('click', function() {
        const message = `দ্বীপ্র মেডিসিন কর্ণার থেকে আপনি ${currentSmsData.date} তারিখে সর্বমোট ${currentSmsData.total} টাকার ${currentSmsData.products} কিনে ${currentSmsData.paid} টাকা পরিশোধ করেছেন এবং আপনার আরো ${currentSmsData.due} টাকা বকেয়া রয়েছে। বিস্তারিত তথ্যের জন্য কল করুন ০১৭১১১৩১৯৩১২। ধন্যবাদ।`;
        
        sendSms(currentSmsData.mobile, message);
    });
    
    // Manual SMS button
    document.getElementById('manualSms').addEventListener('click', function() {
        document.getElementById('manualSmsContainer').style.display = 'block';
        document.getElementById('manualMessage').value = '';
    });
    
    // Send manual SMS
    document.getElementById('sendManualSms').addEventListener('click', function() {
        const message = document.getElementById('manualMessage').value;
        if (message.trim() === '') {
            alert('দয়া করে মেসেজ লিখুন');
            return;
        }
        
        sendSms(currentSmsData.mobile, message);
    });
    
    // Function to send SMS via API
    function sendSms(number, message) {
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=send_sms&number=${encodeURIComponent(number)}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('smsResult').style.display = 'block';
            const responseBody = document.getElementById('responseBody');
            responseBody.innerHTML = '';
            
            if (data.status === 'success' && data.response) {
                for (const [key, value] of Object.entries(data.response)) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${key}</strong></td>
                        <td>${value}</td>
                    `;
                    responseBody.appendChild(row);
                }
            } else {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="2">মেসেজ পাঠাতে সমস্যা হয়েছে: ${JSON.stringify(data)}</td>
                `;
                responseBody.appendChild(row);
            }
            
            // Refresh credit info
            fetchCreditInfo();
        })
        .catch(error => {
            document.getElementById('smsResult').style.display = 'block';
            const responseBody = document.getElementById('responseBody');
            responseBody.innerHTML = `
                <tr>
                    <td colspan="2">মেসেজ পাঠাতে ত্রুটি হয়েছে: ${error.message}</td>
                </tr>
            `;
        });
    }
    
    // Fetch SMS credit info
    function fetchCreditInfo() {
        fetch('put your api')
            .then(response => response.json())
            .then(data => {
                if (data.balance) {
                    document.querySelector('.credit-balance strong').textContent = data.balance;
                }
                if (data.expiry) {
                    document.querySelector('.credit-expiry strong').textContent = data.expiry;
                }
            })
            .catch(error => {
                console.error('Error fetching credit info:', error);
            });
    }
    
    // Refresh history
    document.getElementById('refreshHistory').addEventListener('click', function() {
        window.location.reload();
    });
    
    // Search history
    document.getElementById('historySearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.querySelectorAll('.history-item');
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Initial credit info fetch
    fetchCreditInfo();
    
    // Auto calculate due on page load
    calculateDue();
});