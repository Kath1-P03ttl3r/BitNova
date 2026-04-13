function downloadRecipePdf() {
    const data = window.recipeData;
    if (!data) {
        return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'pt', format: 'a4' });
    const margin = 40;
    const maxWidth = 520;
    let y = margin;
    doc.setFontSize(22);
    doc.text(data.title, margin, y);
    y += 30;
    doc.setFontSize(12);
    doc.setTextColor(90, 60, 40);
    doc.text(`By ${data.author}`, margin, y);
    y += 24;
    doc.setFontSize(14);
    doc.text(`${data.mealType} · ${data.duration}${data.dietaryRestriction ? ' · ' + data.dietaryRestriction : ''}`, margin, y);
    y += 24;
    doc.setFontSize(12);
    const splitDesc = doc.splitTextToSize(data.description, maxWidth);
    doc.text(splitDesc, margin, y);
    y += splitDesc.length * 16 + 18;
    doc.setFontSize(16);
    doc.text('Ingredients', margin, y);
    y += 20;
    doc.setFontSize(12);
    data.ingredients.forEach(item => {
        const line = `• ${item}`;
        const splitLine = doc.splitTextToSize(line, maxWidth);
        doc.text(splitLine, margin, y);
        y += splitLine.length * 16;
    });
    y += 12;
    doc.setFontSize(16);
    doc.text('Instructions', margin, y);
    y += 20;
    doc.setFontSize(12);
    const splitSteps = doc.splitTextToSize(data.steps, maxWidth);
    doc.text(splitSteps, margin, y);
    doc.save(`${data.title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`);
}
