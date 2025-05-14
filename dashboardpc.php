<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: index.html');
    exit;
}

// Check session timeout (30 minutes)
if (isset($_SESSION['last_activity'])) {
    $inactive = 1800; // 30 minutes in seconds
    $session_life = time() - $_SESSION['last_activity'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header('Location: index.html');
        exit;
    }
}
$_SESSION['last_activity'] = time();

$history = [];
if (file_exists('history.json')) {
    $history = json_decode(file_get_contents('history.json'), true);
}

// Get SMS credit
$credit_info = ['balance' => 'Loading...', 'expiry' => ''];
$credit_data = @file_get_contents('put your api');
if ($credit_data !== false) {
    $credit_info = json_decode($credit_data, true);
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>দ্বীপ্র মেডিসিন কর্ণার - ড্যাশবোর্ড</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        @font-face {
            font-family: 'Bangla';
            src: url('bd.ttf') format('truetype');
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <div class="header-content">
                <h1>দ্বীপ্র মেডিসিন কর্ণার</h1>
                <p>ব্যবসায়িক হিসাব ব্যবস্থাপনা সিস্টেম</p>
            </div>
            <div class="credit-info">
                <div class="credit-balance">
                    <span>SMS ব্যালেন্স:</span>
                    <strong><?php echo $credit_info['balance']; ?></strong>
                </div>
                <?php if (!empty($credit_info['expiry'])): ?>
                <div class="credit-expiry">
                    <span>মেয়াদ:</span>
                    <strong><?php echo $credit_info['expiry']; ?></strong>
                </div>
                <?php endif; ?>
            </div>
            <button 
  onclick="window.location.href='personalhisab.php'"
  style="
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: Arial, sans-serif;
    line-height: 1.5;
    min-width: 120px;
  "
  onmouseover="this.style.backgroundColor='#059669'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
  onmouseout="this.style.backgroundColor='#10b981'; this.style.boxShadow='none'"
  onmousedown="this.style.transform='scale(0.98)'"
  onmouseup="this.style.transform='scale(1)'"
>
  <svg width="18" height="18" viewBox="0 0 24 24" fill="white" style="flex-shrink: 0;">
    <path d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4z"/>
  </svg>
  ব্যাক্তিগত হিসাব
</button>
<button
  onclick="window.location.href='logout.php'"
  style="
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: Arial, sans-serif;
    line-height: 1.5;
    min-width: 120px;
  "
  onmouseover="this.style.backgroundColor='#059669'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
  onmouseout="this.style.backgroundColor='#10b981'; this.style.boxShadow='none'"
  onmousedown="this.style.transform='scale(0.98)'"
  onmouseup="this.style.transform='scale(1)'"
>
  <svg width="18" height="18" viewBox="0 0 24 24" fill="white" style="flex-shrink: 0;">
    <path d="M16 17v-3H9v-4h7V7l5 5l-5 5M14 2a2 2 0 0 1 2 2v2h-2V4H5v16h9v-2h2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9z"/>
  </svg>
 লগআউট
</button>
            
        </header>
        
        <div class="main-content">
            <div class="form-container">
                <h2>নতুন হিসাব যোগ করুন</h2>
                <form method="POST" action="process.php" id="accountForm">
                    <div class="form-group">
                        <label for="mobile">গ্রাহকের মোবাইল নাম্বার</label>
                        <input type="text" id="mobile" name="mobile" pattern="01[3-9]\d{8}" title="01 দিয়ে শুরু করে 11 ডিজিটের নাম্বার দিন">
                    </div>
                    
                    <div class="form-group">
                        <label for="name">গ্রাহকের নাম</label>
                        <input type="text" id="name" name="name">
                    </div>
                    
                    <div class="form-group">
                        <label for="products">পণ্য বিবরণ</label>
                        <textarea id="products" name="products"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="total">সর্বমোট টাকা</label>
                            <input type="number" id="total" name="total" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="paid">পরিশোধিত টাকা</label>
                            <input type="number" id="paid" name="paid" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="due">বকেয়া</label>
                            <input type="number" id="due" name="due" min="0" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">তারিখ</label>
                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <button type="submit" name="save" class="submit-btn">
                        <span>সংরক্ষন করুন</span>
                        <svg viewBox="0 0 24 24">
                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z" />
                        </svg>
                    </button>
                </form>
            </div>
            
            <div class="history-container">
                <div class="history-header">
                    <h2>ইতিহাস</h2>
                    <div class="history-actions">
                        <button id="refreshHistory" title="রিফ্রেশ করুন">
                            <svg viewBox="0 0 24 24">
                                <path d="M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z" />
                            </svg>
                        </button>
                        <input type="text" id="historySearch" placeholder="খুঁজুন...">
                    </div>
                </div>
                <div class="history-list" id="historyList">
                    <?php foreach ($history as $entry): ?>
                        <div class="history-item" data-id="<?php echo $entry['id']; ?>">
                            <div class="item-header">
                                <h3><?php echo !empty($entry['name']) ? $entry['name'] : 'নাম নেই'; ?> 
                                <?php if (!empty($entry['mobile'])): ?>
                                    <span>(<?php echo $entry['mobile']; ?>)</span>
                                <?php endif; ?>
                                </h3>
                                <span class="item-date"><?php echo !empty($entry['date']) ? date('d/m/Y', strtotime($entry['date'])) : 'তারিখ নেই'; ?></span>
                            </div>
                            <div class="item-details">
                                <?php if (!empty($entry['products'])): ?>
                                <p><strong>পণ্য:</strong> <?php echo $entry['products']; ?></p>
                                <?php endif; ?>
                                <div class="item-amounts">
                                    <?php if (!empty($entry['total'])): ?>
                                    <div class="amount-box total">
                                        <span>মোট</span>
                                        <strong><?php echo $entry['total']; ?> টাকা</strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['paid'])): ?>
                                    <div class="amount-box paid">
                                        <span>পরিশোধিত</span>
                                        <strong><?php echo $entry['paid']; ?> টাকা</strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['due'])): ?>
                                    <div class="amount-box due">
                                        <span>বকেয়া</span>
                                        <strong><?php echo $entry['due']; ?> টাকা</strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="item-actions">
                                <button class="edit-btn" onclick="openEditModal(
                                    '<?php echo $entry['id']; ?>',
                                    '<?php echo !empty($entry['mobile']) ? htmlspecialchars($entry['mobile'], ENT_QUOTES) : ''; ?>',
                                    '<?php echo !empty($entry['name']) ? htmlspecialchars($entry['name'], ENT_QUOTES) : ''; ?>',
                                    '<?php echo !empty($entry['products']) ? htmlspecialchars($entry['products'], ENT_QUOTES) : ''; ?>',
                                    '<?php echo !empty($entry['total']) ? $entry['total'] : ''; ?>',
                                    '<?php echo !empty($entry['paid']) ? $entry['paid'] : ''; ?>',
                                    '<?php echo !empty($entry['due']) ? $entry['due'] : ''; ?>',
                                    '<?php echo !empty($entry['date']) ? $entry['date'] : ''; ?>'
                                )">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                    </svg>
                                    এডিট
                                </button>
                                <button class="delete-btn" onclick="deleteEntry('<?php echo $entry['id']; ?>')">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                    </svg>
                                    ডিলিট
                                </button>
                                <?php if (!empty($entry['mobile'])): ?>
                                <button class="send-btn" onclick="prepareSms(
                                    '<?php echo $entry['mobile']; ?>',
                                    '<?php echo !empty($entry['name']) ? htmlspecialchars($entry['name'], ENT_QUOTES) : ''; ?>',
                                    '<?php echo !empty($entry['products']) ? htmlspecialchars($entry['products'], ENT_QUOTES) : ''; ?>',
                                    '<?php echo !empty($entry['total']) ? $entry['total'] : ''; ?>',
                                    '<?php echo !empty($entry['paid']) ? $entry['paid'] : ''; ?>',
                                    '<?php echo !empty($entry['due']) ? $entry['due'] : ''; ?>',
                                    '<?php echo !empty($entry['date']) ? $entry['date'] : ''; ?>'
                                )">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
                                    </svg>
                                    সেন্ড
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($history)): ?>
                <div class="empty-history">
                    <svg viewBox="0 0 24 24">
                        <path d="M9.75,20.41C7.53,19.43 5.56,17.87 4.03,15.88L5.59,14.32C6.82,16.05 8.5,17.32 10.42,17.94L9.75,20.41M13.25,20.41L12.58,17.94C14.5,17.32 16.18,16.05 17.41,14.32L18.97,15.88C17.44,17.87 15.47,19.43 13.25,20.41M3,13.5L2,12.5L3,11.5C3,11.5 6,8.5 9,8.5C12,8.5 15.5,11.5 15.5,11.5L16.5,12.5L15.5,13.5C15.5,13.5 12,16.5 9,16.5C6,16.5 3,13.5 3,13.5M18.92,7.08L17.5,8.5C19.75,10.82 19.75,14.5 17.5,16.75L18.92,18.17C22.03,15.04 22.03,10.15 18.92,7.08M14.5,6.25L13.08,4.83C16.19,1.72 21.06,1.72 24.17,4.83L22.75,6.25C20.5,4 16.76,4 14.5,6.25Z" />
                    </svg>
                    <p>কোন হিসাব পাওয়া যায়নি</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>এডিট এন্ট্রি</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="editForm" method="POST" action="process.php">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="edit" value="1">
                
                <div class="form-group">
                    <label for="editMobile">গ্রাহকের মোবাইল নাম্বার</label>
                    <input type="text" id="editMobile" name="mobile" pattern="01[3-9]\d{8}" title="01 দিয়ে শুরু করে 11 ডিজিটের নাম্বার দিন">
                </div>
                
                <div class="form-group">
                    <label for="editName">গ্রাহকের নাম</label>
                    <input type="text" id="editName" name="name">
                </div>
                
                <div class="form-group">
                    <label for="editProducts">পণ্য বিবরণ</label>
                    <textarea id="editProducts" name="products"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editTotal">সর্বমোট টাকা</label>
                        <input type="number" id="editTotal" name="total" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="editPaid">পরিশোধিত টাকা</label>
                        <input type="number" id="editPaid" name="paid" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="editDue">বকেয়া</label>
                        <input type="number" id="editDue" name="due" min="0" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editDate">তারিখ</label>
                    <input type="date" id="editDate" name="date">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="save-btn">
                        <svg viewBox="0 0 24 24">
                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z" />
                        </svg>
                        সেভ করুন
                    </button>
                    <button type="button" class="cancel-btn close-edit-modal">
                        <svg viewBox="0 0 24 24">
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                        </svg>
                        বাতিল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SMS Modal -->
    <div class="modal" id="smsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>মেসেজ অপশন</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="sms-options">
                <button id="autoSms" class="option-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
                    </svg>
                    স্বয়ংক্রিয় মেসেজ
                </button>
                <button id="manualSms" class="option-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                    </svg>
                    ম্যানুয়াল মেসেজ
                </button>
            </div>
            <div id="manualSmsContainer" style="display:none;">
                <textarea id="manualMessage" placeholder="আপনার মেসেজ লিখুন..."></textarea>
                <button id="sendManualSms" class="send-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
                    </svg>
                    সেন্ড
                </button>
            </div>
            <div id="smsResult" class="sms-result" style="display:none;">
                <h4>মেসেজ রেসপন্স</h4>
                <div class="response-table">
                    <table>
                        <tbody id="responseBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>