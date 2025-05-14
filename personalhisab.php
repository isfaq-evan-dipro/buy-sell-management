<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize data directory and files
$data_dir = 'data';
$transactions_file = "$data_dir/transactions.json";
$notes_file = "$data_dir/notes.json";

// Create data directory if not exists
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0755, true);
}

// Initialize empty JSON files if they don't exist
if (!file_exists($transactions_file)) {
    file_put_contents($transactions_file, '[]');
}
if (!file_exists($notes_file)) {
    file_put_contents($notes_file, '[]');
}

// Load data from JSON files
$transactions = json_decode(file_get_contents($transactions_file), true) ?: [];
$notes = json_decode(file_get_contents($notes_file), true) ?: [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new transaction
    if (isset($_POST['add_transaction'])) {
        $new_transaction = [
            'id' => uniqid(),
            'type' => $_POST['type'] ?? 'income',
            'amount' => (float)($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'description' => $_POST['description'] ?? ''
        ];
        array_unshift($transactions, $new_transaction);
        file_put_contents($transactions_file, json_encode($transactions, JSON_PRETTY_PRINT));
    }
    
    // Delete transaction
    if (isset($_POST['delete_id'])) {
        $transactions = array_values(array_filter($transactions, function($t) {
            return $t['id'] !== $_POST['delete_id'];
        }));
        file_put_contents($transactions_file, json_encode($transactions, JSON_PRETTY_PRINT));
    }
    
    // Save note
    if (isset($_POST['save_note'])) {
        $note_id = $_POST['note_id'] ?? uniqid();
        $notes[$note_id] = [
            'title' => $_POST['note_title'] ?? 'নতুন নোট',
            'content' => $_POST['note_content'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($notes_file, json_encode($notes, JSON_PRETTY_PRINT));
    }
    
    // Delete note
    if (isset($_POST['delete_note'])) {
        unset($notes[$_POST['note_id']]);
        file_put_contents($notes_file, json_encode($notes, JSON_PRETTY_PRINT));
    }
}

// Calculate totals function
function calculateTotals($transactions, $period = 'all', $month = null, $year = null) {
    $today = date('Y-m-d');
    $current_month = date('m');
    $current_year = date('Y');
    
    $totals = ['income' => 0, 'expense' => 0];
    
    foreach ($transactions as $t) {
        $t_date = strtotime($t['date']);
        $t_month = date('m', $t_date);
        $t_year = date('Y', $t_date);
        
        $include = false;
        
        if ($period === 'all') {
            $include = true;
        } elseif ($period === 'today') {
            $include = ($t['date'] === $today);
        } elseif ($period === 'month') {
            $include = ($t_month === ($month ?: $current_month)) && ($t_year === ($year ?: $current_year));
        } elseif ($period === 'year') {
            $include = ($t_year === ($year ?: $current_year));
        }
        
        if ($include) {
            $totals[$t['type']] += $t['amount'];
        }
    }
    
    $totals['balance'] = $totals['income'] - $totals['expense'];
    return $totals;
}

// Get selected period from POST or use current
$selected_month = $_POST['selected_month'] ?? date('m');
$selected_year = $_POST['selected_year'] ?? date('Y');
$years = range(2025, 2050);

// Bengali month names
$bengali_months = array(
    '01' => 'জানুয়ারী',
    '02' => 'ফেব্রুয়ারী',
    '03' => 'মার্চ',
    '04' => 'এপ্রিল',
    '05' => 'মে',
    '06' => 'জুন',
    '07' => 'জুলাই',
    '08' => 'আগস্ট',
    '09' => 'সেপ্টেম্বর',
    '10' => 'অক্টোবর',
    '11' => 'নভেম্বর',
    '12' => 'ডিসেম্বর'
);

// Calculate all totals
$daily_totals = calculateTotals($transactions, 'today');
$monthly_totals = calculateTotals($transactions, 'month', $selected_month, $selected_year);
$yearly_totals = calculateTotals($transactions, 'year', null, $selected_year);
$all_totals = calculateTotals($transactions);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>দ্বীপ্র মেডিসিন কর্ণার - হিসাব ব্যবস্থাপনা</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --income: #10b981;
            --expense: #ef4444;
            --primary: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --card-hover: 0 4px 6px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, #2563eb 100%);
            box-shadow: var(--card-shadow);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            box-shadow: var(--card-hover);
            transform: translateY(-2px);
        }
        
        .summary-card {
            border-radius: 10px;
            color: white;
            padding: 15px;
        }
        
        .income-card { background: linear-gradient(135deg, var(--income) 0%, #059669 100%); }
        .expense-card { background: linear-gradient(135deg, var(--expense) 0%, #dc2626 100%); }
        .balance-card { background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%); }
        
        .transaction-income { border-left: 4px solid var(--income); }
        .transaction-expense { border-left: 4px solid var(--expense); }
        
        .note-card {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .note-card:hover, .note-card.active {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .calculator-btn {
            width: 60px;
            height: 60px;
            margin: 5px;
            font-size: 1.25rem;
            border-radius: 10px !important;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--card-hover);
            z-index: 100;
            color: white;
        }
        
        .floating-btn.calculator { 
            right: 30px; 
            background: var(--primary); 
        }
        
        .floating-btn.notes { 
            right: 100px; 
            background: #4b5563; 
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-shop me-2"></i>দ্বীপ্র মেডিসিন কর্ণার
            </a>
            <div class="d-flex align-items-center text-white">
                <i class="bi bi-calendar3 me-2"></i>
                <?= date('d ') . $bengali_months[date('m')] . date(' Y') ?>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Period Selection -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>সময়কাল নির্বাচন</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">মাস</label>
                        <select name="selected_month" class="form-select">
                            <?php foreach ($bengali_months as $num => $month): ?>
                                <option value="<?= $num ?>" <?= $selected_month === $num ? 'selected' : '' ?>>
                                    <?= $month ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
        <label class="form-label">বছর</label>
        <select name="selected_year" class="form-select">
            <?php foreach ($years as $year): ?>
                <option value="<?= $year ?>" <?= $selected_year == $year ? 'selected' : '' ?>>
                    <?= $year ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter-circle me-1"></i>ফিল্টার প্রয়োগ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="summary-card income-card">
                    <h5><i class="bi bi-arrow-down-circle"></i> আজকের আয়</h5>
                    <h2>৳<?= number_format($daily_totals['income'], 2) ?></h2>
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <small>মূলধন</small>
                            <p class="mb-0">৳<?= number_format($daily_totals['expense'], 2) ?></p>
                        </div>
                        <div>
                            <small>লভ্যাংশ</small>
                            <p class="mb-0">৳<?= number_format($daily_totals['balance'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="summary-card expense-card">
                    <h5><i class="bi bi-calendar-month"></i> মাসিক লভ্যাংশ</h5>
                    <h2>৳<?= number_format($monthly_totals['income'] - $monthly_totals['expense'], 2) ?></h2>
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <small>আয়</small>
                            <p class="mb-0">৳<?= number_format($monthly_totals['income'], 2) ?></p>
                        </div>
                        <div>
                            <small>মূলধন</small>
                            <p class="mb-0">৳<?= number_format($monthly_totals['expense'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="summary-card balance-card">
                    <h5><i class="bi bi-wallet2"></i> বার্ষিক লভ্যাংশ</h5>
                    <h2>৳<?= number_format($yearly_totals['income'] - $yearly_totals['expense'], 2) ?></h2>
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <small>আয়</small>
                            <p class="mb-0">৳<?= number_format($yearly_totals['income'], 2) ?></p>
                        </div>
                        <div>
                            <small>মূলধন</small>
                            <p class="mb-0">৳<?= number_format($yearly_totals['expense'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Management -->
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>নতুন লেনদেন যোগ করুন</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="type" id="income" value="income" checked>
                                    <label class="btn btn-outline-success" for="income">
                                        <i class="bi bi-arrow-down-circle me-1"></i> আয়
                                    </label>
                                    <input type="radio" class="btn-check" name="type" id="expense" value="expense">
                                    <label class="btn btn-outline-danger" for="expense">
                                        <i class="bi bi-arrow-up-circle me-1"></i> মূলধন
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">পরিমাণ (৳)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">তারিখ</label>
                                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">বিবরণ <small class="text-muted">(ঐচ্ছিক)</small></label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            
                            <button type="submit" name="add_transaction" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-save me-1"></i> সংরক্ষণ করুন
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>লেনদেন তালিকা</h5>
                        <small class="text-muted">
                            <?= $bengali_months[$selected_month] . ' ' . $selected_year ?>
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="120">তারিখ</th>
                                        <th>বিবরণ</th>
                                        <th width="120" class="text-end">পরিমাণ</th>
                                        <th width="40"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="bi bi-receipt-cutoff fs-1 opacity-25"></i>
                                                <p class="mt-2">কোন লেনদেন নেই</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $t): 
                                            $t_month = date('m', strtotime($t['date']));
                                            $t_year = date('Y', strtotime($t['date']));
                                            if ($t_month === $selected_month && $t_year === $selected_year): ?>
                                            <tr class="transaction-<?= $t['type'] ?>">
                                                <td><?= date('d/m/y', strtotime($t['date'])) ?></td>
                                                <td><?= !empty($t['description']) ? htmlspecialchars($t['description']) : '<span class="text-muted">(বিবরণ নেই)</span>' ?></td>
                                                <td class="text-end <?= $t['type'] === 'income' ? 'text-success' : 'text-danger' ?> fw-bold">
                                                    <?= $t['type'] === 'income' ? '+' : '-' ?>৳<?= number_format($t['amount'], 2) ?>
                                                </td>
                                                <td class="text-end">
                                                    <form method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত?')">
                                                        <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-link text-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculator Modal -->
    <div class="modal fade" id="calculatorModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calculator me-2"></i>ক্যালকুলেটর</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="calculator-display mb-3 form-control form-control-lg text-end" id="calcDisplay">0</div>
                    <div class="calculator-buttons">
                        <div class="row g-2 mb-2">
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('7')">7</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('8')">8</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('9')">9</button></div>
                            <div class="col-3"><button class="btn btn-danger calculator-btn" onclick="calcClear()">C</button></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('4')">4</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('5')">5</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('6')">6</button></div>
                            <div class="col-3"><button class="btn btn-primary calculator-btn" onclick="calcInput('+')">+</button></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('1')">1</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('2')">2</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('3')">3</button></div>
                            <div class="col-3"><button class="btn btn-primary calculator-btn" onclick="calcInput('-')">-</button></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('0')">0</button></div>
                            <div class="col-3"><button class="btn btn-light calculator-btn" onclick="calcInput('.')">.</button></div>
                            <div class="col-3"><button class="btn btn-primary calculator-btn" onclick="calcInput('*')">×</button></div>
                            <div class="col-3"><button class="btn btn-primary calculator-btn" onclick="calcInput('/')">÷</button></div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-success calculator-btn w-100" onclick="calcCalculate()">=</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-journal-text me-2"></i>নোটপ্যাড</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between mb-3">
                                <h5><i class="bi bi-journal-bookmark me-2"></i>আমার নোটসমূহ</h5>
                                <button class="btn btn-sm btn-primary" onclick="newNote()">
                                    <i class="bi bi-plus-lg me-1"></i> নতুন
                                </button>
                            </div>
                            <div class="list-group">
                                <?php if (empty($notes)): ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-journal-x fs-1 opacity-25"></i>
                                        <p class="mt-2">কোন নোট নেই</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notes as $id => $note): ?>
                                        <a href="#" class="list-group-item list-group-item-action note-card" onclick="loadNote('<?= $id ?>')">
                                            <h6 class="mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock-history me-1"></i>
                                                <?= date('d M Y', strtotime($note['updated_at'])) ?>
                                            </small>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <form id="noteForm" method="POST">
                                <input type="hidden" name="note_id" id="noteId">
                                <div class="mb-3">
                                    <input type="text" name="note_title" id="noteTitle" class="form-control form-control-lg" placeholder="নোটের শিরোনাম" required>
                                </div>
                                <div class="mb-3">
                                    <textarea name="note_content" id="noteContent" class="form-control" rows="10" placeholder="নোট লিখুন..." required></textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" name="save_note" class="btn btn-success">
                                        <i class="bi bi-save me-1"></i> সংরক্ষণ
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteNote()">
                                        <i class="bi bi-trash me-1"></i> ডিলিট
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Buttons -->
    <button class="floating-btn notes pulse" data-bs-toggle="modal" data-bs-target="#notesModal">
        <i class="bi bi-journal-text"></i>
    </button>
    
    <button class="floating-btn calculator" data-bs-toggle="modal" data-bs-target="#calculatorModal">
        <i class="bi bi-calculator"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculator functionality
        let calcValue = '0';
        const calcDisplay = document.getElementById('calcDisplay');
        
        function calcInput(val) {
            if (calcValue === '0' && val !== '.') {
                calcValue = val;
            } else {
                calcValue += val;
            }
            calcDisplay.textContent = calcValue;
        }
        
        function calcClear() {
            calcValue = '0';
            calcDisplay.textContent = calcValue;
        }
        
        function calcCalculate() {
            try {
                calcValue = eval(calcValue).toString();
                calcDisplay.textContent = calcValue;
            } catch (e) {
                alert('Invalid calculation');
                calcClear();
            }
        }
        
        // Notes functionality
        function newNote() {
            document.getElementById('noteId').value = '';
            document.getElementById('noteTitle').value = '';
            document.getElementById('noteContent').value = '';
            document.querySelectorAll('.note-card').forEach(card => {
                card.classList.remove('active');
            });
        }
        
        function loadNote(noteId) {
            const notes = <?= json_encode($notes) ?>;
            if (notes[noteId]) {
                document.getElementById('noteId').value = noteId;
                document.getElementById('noteTitle').value = notes[noteId].title;
                document.getElementById('noteContent').value = notes[noteId].content;
                
                document.querySelectorAll('.note-card').forEach(card => {
                    card.classList.remove('active');
                    if (card.textContent.includes(notes[noteId].title)) {
                        card.classList.add('active');
                    }
                });
            }
        }
        
        function confirmDeleteNote() {
            if (confirm('আপনি কি এই নোট ডিলিট করতে চান?')) {
                const form = document.getElementById('noteForm');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_note';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        }
    </script>
</body>
</html>