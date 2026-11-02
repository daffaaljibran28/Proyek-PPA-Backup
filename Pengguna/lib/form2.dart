import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'theme.dart';
import 'header.dart';
import 'konfirmasi.dart';

class Form2Page extends StatefulWidget {
  final String kategori;
  final String sebagai;
  final String lokasi;
  final String tanggal;
  final String kronologi;

  const Form2Page({
    super.key,
    required this.kategori,
    required this.sebagai,
    required this.lokasi,
    required this.tanggal,
    required this.kronologi,
  });

  @override
  State<Form2Page> createState() => _Form2PageState();
}

class _Form2PageState extends State<Form2Page> {
  final ImagePicker _imagePicker = ImagePicker();
  final List<XFile> _listFoto = [];

  void _tampilkanPesanMaksimal() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Maksimal hanya dapat mengunggah 3 foto bukti.',
          style: GoogleFonts.plusJakartaSans(color: Colors.white),
        ),
        backgroundColor: Colors.redAccent,
      ),
    );
  }

  void _bukaPilihFoto() {
    if (_listFoto.length >= 3) {
      _tampilkanPesanMaksimal();
      return;
    }

    showModalBottomSheet<void>(
      context: context,
      builder: (sheetContext) => SafeArea(
        child: Wrap(
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library_outlined),
              title: const Text('Pilih dari galeri'),
              onTap: () {
                Navigator.pop(sheetContext);
                _pilihFoto(ImageSource.gallery);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_camera_outlined),
              title: const Text('Ambil dengan kamera'),
              onTap: () {
                Navigator.pop(sheetContext);
                _pilihFoto(ImageSource.camera);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _pilihFoto(ImageSource sumber) async {
    try {
      final List<XFile> hasil;
      if (sumber == ImageSource.gallery) {
        hasil = await _imagePicker.pickMultiImage(
          imageQuality: 80,
          maxWidth: 1600,
          maxHeight: 1600,
        );
      } else {
        final foto = await _imagePicker.pickImage(
          source: ImageSource.camera,
          imageQuality: 80,
          maxWidth: 1600,
          maxHeight: 1600,
        );
        hasil = foto == null ? [] : [foto];
      }

      if (!mounted || hasil.isEmpty) return;

      final fotoValid = hasil.where((foto) {
        final nama = foto.name.toLowerCase();
        return nama.endsWith('.jpg') ||
            nama.endsWith('.jpeg') ||
            nama.endsWith('.png');
      }).toList();

      if (fotoValid.length != hasil.length) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Format foto harus JPG, JPEG, atau PNG.',
              style: GoogleFonts.plusJakartaSans(color: Colors.white),
            ),
          ),
        );
      }

      if (fotoValid.isEmpty) return;

      final slotTersedia = 3 - _listFoto.length;
      setState(() {
        _listFoto.addAll(fotoValid.take(slotTersedia));
      });

      if (fotoValid.length > slotTersedia) {
        _tampilkanPesanMaksimal();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Foto tidak dapat dipilih. Silakan coba lagi.',
            style: GoogleFonts.plusJakartaSans(color: Colors.white),
          ),
        ),
      );
    }
  }

  void _hapusFoto(int index) {
    setState(() {
      _listFoto.removeAt(index);
    });
  }

  @override
  Widget build(BuildContext context) {
    return BaseLayout(
      showBackButton: true,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        children: [
          // Area Kotak Pilih Foto
          InkWell(
            onTap:
                _listFoto.length < 3 ? _bukaPilihFoto : _tampilkanPesanMaksimal,
            borderRadius: BorderRadius.circular(16),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppTheme.borderPink, width: 1.5),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.add_photo_alternate_outlined,
                    size: 46,
                    color: AppTheme.primaryPink,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Pilih Foto Bukti',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'JPG, JPEG, atau PNG\nPilih dari galeri atau kamera',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),
          Text(
            'Terpilih: ${_listFoto.length} / 3 foto',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          if (_listFoto.isNotEmpty) ...[
            const SizedBox(height: 10),
            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: List.generate(_listFoto.length, (index) {
                return Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: FutureBuilder(
                        future: _listFoto[index].readAsBytes(),
                        builder: (context, snapshot) {
                          if (snapshot.hasData) {
                            return Image.memory(
                              snapshot.data!,
                              width: 88,
                              height: 88,
                              fit: BoxFit.cover,
                            );
                          }
                          return Container(
                            width: 88,
                            height: 88,
                            color: Colors.grey.shade200,
                            alignment: Alignment.center,
                            child: const CircularProgressIndicator(
                              strokeWidth: 2,
                              color: AppTheme.primaryPink,
                            ),
                          );
                        },
                      ),
                    ),
                    Positioned(
                      top: 4,
                      right: 4,
                      child: InkWell(
                        onTap: () => _hapusFoto(index),
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.all(3),
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.close,
                            size: 16,
                            color: AppTheme.primaryPink,
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              }),
            ),
          ],

          const SizedBox(height: 32),

          // Tombol Lanjut
          SizedBox(
            height: 48,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _listFoto.isNotEmpty
                    ? AppTheme.primaryPink
                    : Colors.grey.shade400,
                disabledBackgroundColor: Colors.grey.shade400,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onPressed: _listFoto.isNotEmpty && _listFoto.length <= 3
                  ? () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => KonfirmasiPage(
                            kategori: widget.kategori,
                            sebagai: widget.sebagai,
                            lokasi: widget.lokasi,
                            tanggal: widget.tanggal,
                            kronologi: widget.kronologi,
                            files: List<XFile>.from(_listFoto),
                          ),
                        ),
                      );
                    }
                  : null,
              child: Text(
                'Lanjut',
                style: GoogleFonts.plusJakartaSans(
                  color: Colors.white,
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
