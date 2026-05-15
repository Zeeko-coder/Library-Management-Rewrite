document.addEventListener('DOMContentLoaded', function () {
    const exportBtn = document.getElementById('exportExcel');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            // CSV Content Preparation
            const rows = [
                ["LIBROTECH LIBRARY MANAGEMENT SYSTEM - ANALYTICS REPORT"],
                ["Generated on", reportDate],
                [],
                ["CATEGORY DISTRIBUTION"],
                ["Category", "Borrow Count", "Percentage"]
            ];

            categoryData.forEach(function (cat) {
                const percentage = (totalBorrows > 0) ? Math.round((cat.count / totalBorrows) * 100) : 0;
                rows.push([cat.category, cat.count, percentage + "%"]);
            });

            rows.push([], ["TOP RANKED BOOKS"], ["Rank", "Title", "Borrow Count"]);
            let rank = 1;
            topBooks.forEach(function (book) {
                rows.push([rank++, book.title, book.borrow_count]);
            });

            // Create CSV string
            let csvContent = "data:text/csv;charset=utf-8,";
            rows.forEach(function (rowArray) {
                let row = rowArray.map(value => `"${value}"`).join(",");
                csvContent += row + "\r\n";
            });

            // Trigger Download
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `LibroTech_Analytics_${reportFileNameDate}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
