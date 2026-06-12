const { jsPDF } = window.jspdf;

$(document).ready(function () {
    const qrcode = new QRCode("qrcode", {
        text: "reservationid;" + JSON.parse(document.getElementById('reservation').textContent)['reservation_id'],
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
});

$('#download-billet').click(function () {
    const reservationData = JSON.parse(document.getElementById('reservation').textContent);
    const doc = new jsPDF();
    const logoLoc = "/image/car_vikingTransport.png";
    const img = new Image();
    img.src = logoLoc;

    img.onload = function () {

        doc.setFillColor(229, 9, 20);
        doc.rect(0, 0, 210, 40, 'F');

        doc.setTextColor(255, 255, 255);
        doc.setFontSize(22);
        doc.setFont("helvetica", "bold");
        doc.text("BILLET DE RÉSERVATION", 15, 25);
        doc.addImage(img, 'PNG', 125, 10, 614 / 8, 197 / 8);

        doc.setTextColor(40, 40, 40);

        doc.setFontSize(14);
        doc.text(`Réservation n° ${reservationData.cliNum}/${reservationData.reservation_id}`, 15, 55);

        doc.setDrawColor(200, 200, 200);
        doc.line(15, 60, 195, 60);

        doc.setFontSize(12);

        doc.setFont("helvetica", "normal");
        doc.text("Départ :", 15, 75);
        doc.setFont("helvetica", "bold");
        doc.text(`Le ${document.getElementById('dateDepart').textContent}`, 45, 75);

        doc.setFont("helvetica", "normal");
        doc.text("Prix Total :", 15, 90);
        doc.setFont("helvetica", "bold");
        doc.text(`${reservationData.prix} €`, 45, 90);

        doc.setFont("helvetica", "normal");
        doc.text("Points gagnés :", 15, 105);
        doc.setFont("helvetica", "bold");
        doc.text(`${reservationData.points} pts`, 45, 105);

        const qrElement = document.getElementById('qrcode').querySelector('img');
        if (qrElement) {
            doc.addImage(qrElement.src, 'PNG', 140, 70, 45, 45);
        }

        doc.setFontSize(10);
        doc.setFont("helvetica", "italic");
        doc.setTextColor(120, 120, 120);
        doc.text("Merci de voyager avec Viking Transport !", 105, 140, { align: "center" });

        doc.save(`billet_${reservationData.reservation_id}.pdf`);
    };
});
