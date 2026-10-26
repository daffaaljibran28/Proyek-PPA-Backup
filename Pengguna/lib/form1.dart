import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'theme.dart';
import 'header.dart';
import 'form2.dart';
import 'map_picker_dialog.dart';

class Form1Page extends StatefulWidget {
  const Form1Page({super.key});

  @override
  State<Form1Page> createState() => _Form1PageState();
}

class _Form1PageState extends State<Form1Page> {
  String _kategori = 'Kekerasan terhadap Perempuan';
  String _sebagai = 'Korban';
  final TextEditingController _lokasiCtrl = TextEditingController();
  final TextEditingController _tanggalCtrl = TextEditingController();
  final TextEditingController _kronologiCtrl = TextEditingController();

  final List<String> _listKategori = [
    'Kekerasan terhadap Perempuan',
    'Kekerasan terhadap Anak',
    'Pelecehan Seksual',
    'Kekerasan Dalam Rumah Tangga (KDRT)',
  ];

  final List<String> _listSebagai = ['Korban', 'Saksi', 'Keluarga Korban'];

  Future<void> _pilihTanggal() async {
    final DateTime now = DateTime.now();
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: now,
      firstDate: DateTime(2000),
      lastDate: now,
      helpText: 'PILIH TANGGAL KEJADIAN',
      cancelText: 'BATAL',
      confirmText: 'PILIH',
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            textTheme: GoogleFonts.plusJakartaSansTextTheme(
                Theme.of(context).textTheme),
            colorScheme: const ColorScheme.light(
              primary: AppTheme.primaryPink,
              onPrimary: Colors.white,
              onSurface: Colors.black87,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _tanggalCtrl.text =
            "${picked.day.toString().padLeft(2, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.year}";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return BaseLayout(
      showBackButton: true,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        children: [
          _label('Kategori Laporan'),
          _dropdownContainer(
            DropdownButton<String>(
              isExpanded: true,
              value: _kategori,
              underline: const SizedBox(),
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 13, color: Colors.black87),
              items: _listKategori
                  .map((k) => DropdownMenuItem(
                      value: k,
                      child: Text(k,
                          style: GoogleFonts.plusJakartaSans(
                              fontSize: 13, color: Colors.black87))))
                  .toList(),
              onChanged: (v) => setState(() => _kategori = v!),
            ),
          ),
          const SizedBox(height: 16),
          _label('Sebagai'),
          _dropdownContainer(
            DropdownButton<String>(
              isExpanded: true,
              value: _sebagai,
              underline: const SizedBox(),
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 13, color: Colors.black87),
              items: _listSebagai
                  .map((s) => DropdownMenuItem(
                      value: s,
                      child: Text(s,
                          style: GoogleFonts.plusJakartaSans(
                              fontSize: 13, color: Colors.black87))))
                  .toList(),
              onChanged: (v) => setState(() => _sebagai = v!),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.between,
            children: [
              _label('Lokasi Kejadian'),
              InkWell(
                onTap: () async {
                  final selected = await showDialog<String>(
                    context: context,
                    builder: (context) =>
                        MapPickerDialog(initialLocation: _lokasiCtrl.text),
                  );
                  if (selected != null && selected.isNotEmpty) {
                    setState(() {
                      _lokasiCtrl.text = selected;
                    });
                  }
                },
                child: Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Row(
                    children: [
                      const Icon(Icons.map_outlined,
                          size: 14, color: AppTheme.primaryPink),
                      const SizedBox(width: 4),
                      Text(
                        'Pilih di Peta (Google Maps)',
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryPink,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          TextField(
            controller: _lokasiCtrl,
            style: GoogleFonts.plusJakartaSans(
                fontSize: 13, color: Colors.black87),
            decoration: _inputDeco('Isi lokasi kejadian secara lengkap...',
                Icons.location_on_outlined),
          ),
          const SizedBox(height: 16),
          _label('Tanggal Kejadian'),
          InkWell(
            onTap: _pilihTanggal,
            borderRadius: BorderRadius.circular(12),
            child: IgnorePointer(
              child: TextField(
                controller: _tanggalCtrl,
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 13, color: Colors.black87),
                decoration: _inputDeco(
                    'Pilih tanggal kejadian...', Icons.calendar_today_outlined),
              ),
            ),
          ),
          const SizedBox(height: 16),
          _label('Penjelasan Kejadian'),
          TextField(
            controller: _kronologiCtrl,
            maxLines: 4,
            style: GoogleFonts.plusJakartaSans(
                fontSize: 13, color: Colors.black87),
            decoration: InputDecoration(
              hintText: 'Ceritakan kronologi kejadian secara singkat...',
              hintStyle: GoogleFonts.plusJakartaSans(
                  fontSize: 13, color: Colors.grey.shade400),
              filled: true,
              fillColor: Colors.white,
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppTheme.borderPink)),
              enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppTheme.borderPink)),
              focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                      color: AppTheme.primaryPink, width: 1.5)),
            ),
          ),
          const SizedBox(height: 28),
          SizedBox(
            height: 48,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryPink,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12))),
              onPressed: () async {
                if (_lokasiCtrl.text.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Harap isi lokasi kejadian!',
                          style:
                              GoogleFonts.plusJakartaSans(color: Colors.white)),
                    ),
                  );
                  return;
                }
                if (_tanggalCtrl.text.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Harap pilih tanggal kejadian!',
                          style:
                              GoogleFonts.plusJakartaSans(color: Colors.white)),
                    ),
                  );
                  return;
                }
                if (_kronologiCtrl.text.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Harap isi kronologi kejadian!',
                          style:
                              GoogleFonts.plusJakartaSans(color: Colors.white)),
                    ),
                  );
                  return;
                }
                final hasil = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => Form2Page(
                      kategori: _kategori,
                      sebagai: _sebagai,
                      lokasi: _lokasiCtrl.text,
                      tanggal: _tanggalCtrl.text,
                      kronologi: _kronologiCtrl.text,
                    ),
                  ),
                );
                if (hasil == true && mounted) Navigator.pop(context, true);
              },
              child: Text(
                'Lanjut',
                style: GoogleFonts.plusJakartaSans(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _label(String t) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(
          t,
          style: GoogleFonts.plusJakartaSans(
              fontSize: 13, fontWeight: FontWeight.w600, color: Colors.black87),
        ),
      );

  Widget _dropdownContainer(Widget w) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 14),
        decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.borderPink)),
        child: w,
      );

  InputDecoration _inputDeco(String hint, IconData icon) => InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.plusJakartaSans(
            fontSize: 13, color: Colors.grey.shade400),
        prefixIcon: Icon(icon, color: AppTheme.primaryPink, size: 20),
        filled: true,
        fillColor: Colors.white,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppTheme.borderPink)),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppTheme.borderPink)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                const BorderSide(color: AppTheme.primaryPink, width: 1.5)),
      );
}
