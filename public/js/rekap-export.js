class RekapExporter {
    constructor(kerjasamaId, month = null, baseUrl = "/api/v1/all-rekap-export", startDate = null, endDate = null) {
        this.kerjasamaId = kerjasamaId;
        this.month = month || new Date().toISOString().slice(0, 7);
        const params = new URLSearchParams({ month: this.month });
        if (startDate && endDate) {
            params.set('start_date', startDate);
            params.set('end_date', endDate);
        }
        this.apiUrl = `${baseUrl}/${kerjasamaId}?${params}`;
    }

    async fetchAllData() {
        const response = await fetch(this.apiUrl);
        if (!response.ok) throw new Error("Failed to fetch data");
        return await response.json();
    }

    async exportToExcel() {
        const result = await this.fetchAllData();
        if (!result.success) throw new Error(result.message);
        const data = result.data;
        const wb = XLSX.utils.book_new();

        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(
                this.formatOvertimes(data.overtimes, true),
            ),
            "Rekap Lembur",
        );
        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(
                this.formatPersonOuts(data.person_outs, true),
            ),
            "Rekap Personil Keluar",
        );
        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(
                this.formatPersonIns(data.person_ins, true),
            ),
            "Rekap Personil Masuk",
        );
        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(this.formatCuttings(data.cuttings, true)),
            "Rekap Cutting",
        );
        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(
                this.formatFinishedTrainings(data.finished_trainings, true),
            ),
            "Rekap Lepas Training",
        );
        XLSX.utils.book_append_sheet(
            wb,
            XLSX.utils.json_to_sheet(
                this.formatKeteranganLanjutan(data.keterangan_lanjutan, true),
            ),
            "Rekap Keterangan Lanjutan",
        );

        XLSX.writeFile(
            wb,
            `Data_Rekap_${data.client.name}_${new Date().toISOString().split("T")[0]}.xlsx`,
        );
    }

    async exportToPDF() {
        const result = await this.fetchAllData();
        if (!result.success) throw new Error(result.message);
        const data = result.data;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF("l", "mm", "a4");

        let y = 14;
        doc.setFontSize(14);
        doc.text(`Data Rekap - ${data.client.name}`, 14, y);
        y += 6;
        doc.setFontSize(10);
        doc.text(`Periode: ${data.period}`, 14, y);
        y += 6;

        y = this.addSectionTable(
            doc,
            y,
            "Rekap Lembur",
            this.getOvertimeHeaders(),
            this.toBody(this.formatOvertimes(data.overtimes, true)),
            2,
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Personil Keluar",
            this.getPersonOutHeaders(),
            this.toBody(this.formatPersonOuts(data.person_outs, true)),
            2,
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Personil Masuk",
            this.getPersonInHeaders(),
            this.toBody(this.formatPersonIns(data.person_ins, true)),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Cutting",
            this.getCuttingHeaders(),
            this.toBody(this.formatCuttings(data.cuttings, true)),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Lepas Training",
            this.getFinishedTrainingHeaders(),
            this.toBody(
                this.formatFinishedTrainings(data.finished_trainings, true),
            ),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Keterangan Lanjutan",
            this.getKeteranganLanjutanHeaders(),
            this.toBody(
                this.formatKeteranganLanjutan(data.keterangan_lanjutan, true),
            ),
            2,
        );

        doc.save(
            `Data_Rekap_${data.client.name}_${new Date().toISOString().split("T")[0]}.pdf`,
        );
    }

    addSectionTable(doc, y, title, headers, body, groupColumnIndex = null) {
        if (y > 170) {
            doc.addPage();
            y = 14;
        }
        doc.setFontSize(11);
        doc.setFont(undefined, "bold");
        doc.text(title, 14, y);
        y += 3;

        const groupBoundaryRows = groupColumnIndex !== null
            ? this.getGroupBoundaryRows(body, groupColumnIndex)
            : [];

        doc.autoTable({
            head: [headers],
            body,
            startY: y,
            styles: { fontSize: 8, lineWidth: 0.15, lineColor: [203, 213, 225] },
            headStyles: { fillColor: [37, 99, 235] },
            theme: "grid",
            didDrawCell: (data) => {
                if (data.section === "body" && groupBoundaryRows.includes(data.row.index)) {
                    doc.setDrawColor(107, 114, 128);
                    doc.setLineWidth(0.55);
                    doc.line(
                        data.cell.x,
                        data.cell.y + data.cell.height,
                        data.cell.x + data.cell.width,
                        data.cell.y + data.cell.height,
                    );
                }
            },
        });
        return doc.lastAutoTable.finalY + 6;
    }

    getGroupBoundaryRows(body, groupColumnIndex) {
        if (!Array.isArray(body) || body.length === 0) return [];
        const boundaries = [];
        for (let i = 0; i < body.length - 1; i += 1) {
            if (body[i][groupColumnIndex] !== body[i + 1][groupColumnIndex]) {
                boundaries.push(i);
            }
        }
        return boundaries;
    }

    toBody(rows) {
        return rows.map((r) => Object.values(r));
    }

    // ===== formatters sinkron logic lama =====
    formatOvertimes(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];
        const grouped = {};
        d.forEach((item) => {

            const key = item.user?.id || "unknown";
            if (!grouped[key])
                grouped[key] = {
                    nama: (item.user?.nama_lengkap || "-").toUpperCase(),
                    mitra: (item.user?.kerjasama?.client?.name || "-").toUpperCase(),
                    posisi: (item.user?.jabatan?.name_jabatan || "-").toUpperCase(),
                    tanggal: [],
                    kerjasama_id: item.user?.kerjasama_id,
                    hari: 0,
                    jam: [],
                    lainnya: [],
                };

            const dates = Array.isArray(item.tgl)
                ? item.tgl
                : item.tgl
                    ? [item.tgl]
                    : [];

            grouped[key].tanggal.push(...dates);
            const t = (item.type_overtime || "").toLowerCase();
            if (t === "shift") grouped[key].hari += item.count || 1;
            else if (t === "jam" && item.type_overtime_manual)
                grouped[key].jam.push(item.type_overtime_manual);
            else if (t === "lainnya" && item.type_overtime_manual)
                grouped[key].lainnya.push(item.type_overtime_manual);
        });
        // Sort berdasarkan kerjasama_id, jabatan_id, nama_lengkap (match controller order)
        const sortedKeys = Object.keys(grouped).sort((a, b) => {
            const userA = grouped[a];
            const userB = grouped[b];

            // Sort by kerjasama_id
            if (userA.kerjasama_id !== userB.kerjasama_id) {
                return userA.kerjasama_id - userB.kerjasama_id;
            }


            // Sort by nama (fallback)
            return userA.nama.localeCompare(userB.nama);
        });

        const rows = sortedKeys.map((key, i) => {
            const tanggal = this.fmtOvertimeDates(grouped[key].tanggal);

            return {
                no: i + 1,
                nama_karyawan: grouped[key].nama,
                mitra_kerja: grouped[key].mitra,
                posisi: grouped[key].posisi,
                tanggal: tanggal || "-",
                hari: grouped[key].hari > 0
                    ? `${grouped[key].hari} hari`
                    : "-",
                jam: grouped[key].jam.length
                    ? grouped[key].jam.join(", ")
                    : "-",
                lainnya: grouped[key].lainnya.length
                    ? grouped[key].lainnya.join(", ")
                    : "-",
            };
        });
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama_karyawan: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    posisi: "-",
                    tanggal: "-",
                    hari: "-",
                    jam: "-",
                    lainnya: "-",
                },
            ];
        return rows;
    }

    formatPersonOuts(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];
        const rows = d.map((item, i) => ({
            no: i + 1,
            nama_karyawan: (item.user?.nama_lengkap || "-").toUpperCase(),
            mitra_kerja: (item.user?.kerjasama?.client?.name || "-").toUpperCase(),
            posisi: (item.user?.jabatan?.name_jabatan || "-").toUpperCase(),
            jumlah_mk: item.total_mk || "0",
            tanggal_keluar: this.fmt(item.out_date) || "-",
            tanggal_dibuat: this.fmt(item.created_at) || "-"
        }));
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama_karyawan: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    posisi: "-",
                    jumlah_mk: "-",
                    tanggal_keluar: "-",
                    tanggal_dibuat: "-",
                },
            ];
        return rows;
    }

    formatPersonIns(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];
        const rows = d.map((r, i) => ({
            no: i + 1,
            nama: (r.fullname || r.user?.nama_lengkap || "-").toUpperCase(),
            mitra_kerja: (r.client_name || r.client?.name || "-").toUpperCase(),
            jabatan: (r.jabatan?.name_jabatan || "-").toUpperCase(),
            tanggal_masuk: this.fmt(r.date_in),
            jumlah_mk: r.total_mk || '-',
            metode_gaji: (r.method_salary || "-").toUpperCase(),
            no_rek: (r.method_salary_manual || "-").toUpperCase(),
        }));
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    jabatan: "-",
                    tanggal_masuk: "-",
                    jumlah_mk: "0",
                    metode_gaji: "-",
                    no_rek: "-",
                },
            ];
        return rows;
    }

    formatCuttings(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];
        const grouped = {};
        d.forEach((item) => {

            const key = item.user?.id || "unknown";
            if (!grouped[key])
                grouped[key] = {
                    nama: (item.user?.nama_lengkap || "-").toUpperCase(),
                    mitra: (item.user?.kerjasama?.client?.name || "-").toUpperCase(),
                    tanggal: [],
                    kerjasama_id: item.user?.kerjasama_id,
                    hari: item.count,
                    keterangan: [],
                };

            const dates = Array.isArray(item.tgl)
                ? item.tgl
                : item.tgl
                    ? [item.tgl]
                    : [];

            const descArr = Array.isArray(item.desc)
                ? item.desc
                : item.desc ? [item.desc] : [];

            grouped[key].tanggal.push(...dates);
            grouped[key].keterangan.push(
                ...descArr
                    .map((desc) => {
                        if (typeof desc === "object" && desc !== null) {
                            return Object.values(desc)
                                .filter(Boolean)
                                .join(" - ");
                        }

                        return String(desc).trim();
                    })
                    .filter(Boolean)
            );
        });
        // Sort berdasarkan kerjasama_id, jabatan_id, nama_lengkap (match controller order)
        const sortedKeys = Object.keys(grouped).sort((a, b) => {
            const userA = grouped[a];
            const userB = grouped[b];

            // Sort by kerjasama_id
            if (userA.kerjasama_id !== userB.kerjasama_id) {
                return userA.kerjasama_id - userB.kerjasama_id;
            }

            // Sort by nama (fallback)
            return userA.nama.localeCompare(userB.nama);
        });

        const rows = sortedKeys.map((key, i) => {
            const tanggal = this.fmtOvertimeDates(grouped[key].tanggal);
            return {
                no: i + 1,
                nama_karyawan: grouped[key].nama,
                mitra_kerja: grouped[key].mitra,
                tanggal_cutting: tanggal || "-",
                hari: grouped[key].hari > 0
                    ? `${grouped[key].hari} hari`
                    : "-",
                keterangan: grouped[key].keterangan.length
                    ? grouped[key].keterangan.join(", ")
                    : "-"
            };
        });
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    tanggal_cutting: "-",
                    hari: "-",
                    keterangan: "-",
                },
            ];
        return rows;
    }

    formatFinishedTrainings(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];
        const rows = d.map((r, i) => ({
            no: i + 1,
            nama: (r.user?.nama_lengkap || "-").toUpperCase(),
            mitra_kerja: (r.user?.kerjasama?.client?.name || "-").toUpperCase(),
            tanggal_masuk: this.fmt(r.date_in),
            tanggal_selesai: this.fmt(r.date_finish_train),
            keterangan: (r.desc || "-").toUpperCase(),
        }));
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    tanggal_masuk: "-",
                    tanggal_selesai: "-",
                    keterangan: "-",
                },
            ];
        return rows;
    }

    formatKeteranganLanjutan(d, showEmpty = false) {
        if (!Array.isArray(d)) d = [];

        const rows = d.map((r, i) => {
            // Format keterangan: if array of {periode, judul, keterangan}, join them nicely
            let keteranganText = "-";
            if (Array.isArray(r.keterangan)) {
                const items = r.keterangan.map(e => {
                    if (typeof e === 'object') {
                        const parts = [];
                        if (e.periode) parts.push(e.periode.toUpperCase());
                        if (e.judul) parts.push(e.judul.toUpperCase());
                        if (e.keterangan) parts.push(e.keterangan.toUpperCase());
                        return parts.join(" - ");
                    }
                    return (e || "").toUpperCase();
                }).filter(item => item);
                keteranganText = items.join("\n");
            } else if (r.keterangan) {
                keteranganText = r.keterangan.toUpperCase();
            }

            return {
                no: i + 1,
                nama_karyawan: (r.user?.nama_lengkap || "-").toUpperCase(),
                mitra_kerja: (r.user?.kerjasama?.client?.name || "-").toUpperCase(),
                posisi: (r.user?.jabatan?.name_jabatan || "-").toUpperCase(),
                tanggal: this.fmt(r.created_at),
                keterangan: keteranganText,
            };
        });
        if (!rows.length && showEmpty)
            return [
                {
                    no: "-",
                    nama_karyawan: "TIDAK ADA DATA",
                    mitra_kerja: "-",
                    posisi: "-",
                    tanggal: "-",
                    keterangan: "-",
                },
            ];
        return rows;
    }

    getOvertimeHeaders() {
        return [
            "No",
            "Nama Karyawan",
            "Mitra Kerja",
            "Posisi",
            "Tanggal",
            "Hari",
            "Jam",
            "Lainnya",
        ];
    }
    getPersonOutHeaders() {
        return ["No", "Nama Karyawan", "Mitra Kerja", "Posisi", "Jumlah MK", "Tanggal Keluar", "Tanggal Dibuat"];
    }
    getPersonInHeaders() {
        return [
            "No",
            "Nama Karyawan",
            "Mitra Kerja",
            "Jabatan",
            "Tanggal Masuk",
            "Jumlah MK",
            "Metode Gaji",
            "No Rek",
        ];
    }
    getCuttingHeaders() {
        return [
            "No",
            "Nama Karyawan",
            "Mitra Kerja",
            "Tanggal Cutting",
            "hari",
            "Keterangan",
        ];
    }
    getFinishedTrainingHeaders() {
        return ["No", "Nama Karyawan", "Mitra Kerja", "Tanggal Masuk", "Tanggal Selesai", "Keterangan"];
    }
    getKeteranganLanjutanHeaders() {
        return ["No", "Nama Karyawan", "Mitra Kerja", "Posisi", "Tanggal", "Keterangan"];
    }

    parseDate(v) {
        if (!v) return null;
        if (v instanceof Date) return isNaN(v) ? null : v;

        const match = String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
        const d = match
            ? new Date(+match[1], +match[2] - 1, +match[3])
            : new Date(v);

        return isNaN(d) ? null : d;
    }

    monthYear(d) {
        return d.toLocaleDateString("id-ID", { month: "long", year: "numeric" });
    }

    fmt(v) {
        const d = this.parseDate(v);
        return d ? `${d.getDate()} ${this.monthYear(d)}` : "-";
    }

    fmtOvertimeDates(values) {
        const dates = [...new Set(values)]
            .map((value) => this.parseDate(value))
            .filter(Boolean)
            .sort((a, b) => a - b);

        if (!dates.length) return "-";

        const groups = new Map();
        dates.forEach((date) => {
            const key = `${date.getFullYear()}-${date.getMonth()}`;
            groups.set(key, [...(groups.get(key) || []), date]);
        });

        return [...groups.values()]
            .map((group) => `${group.map((date) => date.getDate()).join(", ")} ${this.monthYear(group[0])}`)
            .join(", ");
    }

    prepareSheetFromJson(rows) {
        const sheet = XLSX.utils.json_to_sheet(rows);
        if (!sheet || !sheet['!ref']) return sheet;
        const range = XLSX.utils.decode_range(sheet['!ref']);
        const numCols = range.e.c - range.s.c + 1;
        const numRows = range.e.r - range.s.r + 1;

        // A4 landscape width in mm minus left/right margins used in PDF export
        const a4LandscapeMm = 297;
        const marginLeftMm = 14;
        const marginRightMm = 14;
        const usableWidthMm = a4LandscapeMm - marginLeftMm - marginRightMm;

        // Rough conversion: 1 mm ~= 3.78 px, 1 Excel 'wch' ~= 7 px => wch per mm ~= 0.54
        const mmToWch = 0.54;
        const colMm = usableWidthMm / Math.max(1, numCols);
        const colWch = Math.max(8, Math.round(colMm * mmToWch));

        sheet['!cols'] = Array.from({ length: numCols }, () => ({ wch: colWch }));

        // Row heights (points). Header slightly taller to mimic PDF header style.
        const headerHpt = 18;
        const bodyHpt = 16;
        sheet['!rows'] = Array.from({ length: numRows }, (_, i) => ({ hpt: i === 0 ? headerHpt : bodyHpt }));

        return sheet;
    }

    // Global Export Methods
    withStatus(rows, source, enabled) {
        if (!enabled) return rows;
        return rows.map((row, i) => ({ ...row, status: (source?.[i]?.status || '-').toUpperCase() }));
    }

    exportGlobalToExcel(data) {
        const wb = XLSX.utils.book_new();
        const withStatus = !!data.show_status;

        const sheet1 = this.prepareSheetFromJson(this.withStatus(this.formatOvertimes(data.overtimes, true), data.overtimes, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet1, "Rekap Lembur");

        const sheet2 = this.prepareSheetFromJson(this.withStatus(this.formatPersonOuts(data.person_outs, true), data.person_outs, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet2, "Rekap Personil Keluar");

        const sheet3 = this.prepareSheetFromJson(this.withStatus(this.formatPersonIns(data.person_ins, true), data.person_ins, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet3, "Rekap Personil Masuk");

        const sheet4 = this.prepareSheetFromJson(this.withStatus(this.formatCuttings(data.cuttings, true), data.cuttings, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet4, "Rekap Cutting");

        const sheet5 = this.prepareSheetFromJson(this.withStatus(this.formatFinishedTrainings(data.finished_trainings, true), data.finished_trainings, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet5, "Rekap Lepas Training");

        const sheet6 = this.prepareSheetFromJson(this.withStatus(this.formatKeteranganLanjutan(data.keterangan_lanjutan, true), data.keterangan_lanjutan, withStatus));
        XLSX.utils.book_append_sheet(wb, sheet6, "Rekap Keterangan Lanjutan");

        XLSX.writeFile(
            wb,
            `Data_Rekap_${data.period}_${new Date().toISOString().split("T")[0]}.xlsx`,
        );
    }

    exportGlobalToPDF(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF("l", "mm", "a4");
        const withStatus = !!data.show_status;

        let y = 14;
        doc.setFontSize(14);
        doc.text(`Data Rekap - ${data.period}`, 14, y);
        y += 6;
        doc.setFontSize(10);
        doc.text(`Data Keseluruhan ${data.client?.name || 'Semua Mitra'}`, 14, y);
        y += 6;

        y = this.addSectionTable(
            doc,
            y,
            "Rekap Lembur",
            withStatus ? [...this.getOvertimeHeaders(), 'Status'] : this.getOvertimeHeaders(),
            this.toBody(this.withStatus(this.formatOvertimes(data.overtimes, true), data.overtimes, withStatus)),
            2,
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Personil Keluar",
            withStatus ? [...this.getPersonOutHeaders(), 'Status'] : this.getPersonOutHeaders(),
            this.toBody(this.withStatus(this.formatPersonOuts(data.person_outs, true), data.person_outs, withStatus)),
            2,
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Personil Masuk",
            withStatus ? [...this.getPersonInHeaders(), 'Status'] : this.getPersonInHeaders(),
            this.toBody(this.withStatus(this.formatPersonIns(data.person_ins, true), data.person_ins, withStatus)),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Cutting",
            withStatus ? [...this.getCuttingHeaders(), 'Status'] : this.getCuttingHeaders(),
            this.toBody(this.withStatus(this.formatCuttings(data.cuttings, true), data.cuttings, withStatus)),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Lepas Training",
            withStatus ? [...this.getFinishedTrainingHeaders(), 'Status'] : this.getFinishedTrainingHeaders(),
            this.toBody(this.withStatus(this.formatFinishedTrainings(data.finished_trainings, true), data.finished_trainings, withStatus)),
        );
        y = this.addSectionTable(
            doc,
            y,
            "Rekap Keterangan Lanjutan",
            withStatus ? [...this.getKeteranganLanjutanHeaders(), 'Status'] : this.getKeteranganLanjutanHeaders(),
            this.toBody(this.withStatus(this.formatKeteranganLanjutan(data.keterangan_lanjutan, true), data.keterangan_lanjutan, withStatus)),
            2,
        );

        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);

        window.open(pdfUrl, '_blank');

        // doc.save(
        //     `Data_Rekap_${data.period}_${new Date().toISOString().split("T")[0]}.pdf`,
        // );
    }
}
