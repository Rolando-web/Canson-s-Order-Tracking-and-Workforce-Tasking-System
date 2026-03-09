const fs = require('fs');
let content = fs.readFileSync('d:/IT12-Activity-Project/IT12-Project/resources/views/pages/assignments.blade.php', 'utf8');

// ==========================================
// REPLACE FIRST SCRIPT BLOCK
// ==========================================
const firstStart = '    <script>\n    const _unassignedCount';
const firstEnd = '    }\n    </script>';
const startIdx1 = content.indexOf(firstStart);
const endIdx1 = content.indexOf(firstEnd, startIdx1);

if (startIdx1 !== -1 && endIdx1 !== -1) {
    const endPos1 = endIdx1 + firstEnd.length;
    const newFirstScript =
        '    <script>\n' +
        '    const _unassignedCount = {{ count($availableOrders) }};\n' +
        '    const _activeCount = {{ count($activeOrders ?? []) }};\n' +
        "    const _activeOrdersData = @json(collect($activeOrders ?? [])->keyBy('order_id'));\n" +
        '    </script>';
    content = content.slice(0, startIdx1) + newFirstScript + content.slice(endPos1);
    console.log('First script block replaced.');
} else {
    console.log('ERROR: First script block NOT found. startIdx=' + startIdx1 + ' endIdx=' + endIdx1);
    process.exit(1);
}

// ==========================================
// REPLACE SECOND SCRIPT BLOCK
// ==========================================
// Second block starts with: "\n<script>\nlet currentEmployeeName"
// and ends with: "\n</script>\n@endsection"
const secondStart = '\n<script>\nlet currentEmployeeName';
const secondEnd = '\n</script>\n@endsection';
const startIdx2 = content.indexOf(secondStart);
const endIdx2 = content.indexOf(secondEnd, startIdx2);

if (startIdx2 !== -1 && endIdx2 !== -1) {
    const endPos2 = endIdx2 + secondEnd.length;
    const newSecondScript =
        '\n<script>\n' +
        'const assignmentsData = @json($assignmentsData ?? []);\n' +
        'const availableOrders = @json($availableOrders ?? []);\n' +
        'const workersData = @json($workers ?? []);\n' +
        '</script>\n' +
        '@endsection';
    content = content.slice(0, startIdx2) + newSecondScript + content.slice(endPos2);
    console.log('Second script block replaced.');
} else {
    console.log('ERROR: Second script block NOT found. startIdx=' + startIdx2 + ' endIdx=' + endIdx2);
    process.exit(1);
}

fs.writeFileSync('d:/IT12-Activity-Project/IT12-Project/resources/views/pages/assignments.blade.php', content, 'utf8');
console.log('Done. File saved.');
