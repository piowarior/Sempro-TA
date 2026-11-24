import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ReportActivityScreen extends StatefulWidget {
  final int planId;
  final String planName;
  final int? scheduleId;
  final String scheduledDate;

  const ReportActivityScreen({
    super.key,
    required this.planId,
    required this.planName,
    this.scheduleId,
    required this.scheduledDate,
  });

  @override
  State<ReportActivityScreen> createState() => _ReportActivityScreenState();
}

class _ReportActivityScreenState extends State<ReportActivityScreen> {
  String _executionStatus = 'dilaksanakan';
  final _notesController = TextEditingController();
  bool _isSubmitting = false;
  String? _errorMessage;

  Future<void> _submitReport() async {
    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.submitReport(
        planId: widget.planId,
        activityDate: widget.scheduledDate,
        executionStatus: _executionStatus,
        patientNotes: _notesController.text.trim(),
        scheduleId: widget.scheduleId,
      );

      if (!mounted) return;

      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Laporan aktivitas berhasil dikirim dan tersinkronisasi.'),
            backgroundColor: Color(0xFF059669),
          ),
        );
        Navigator.of(context).pop(true);
      } else {
        setState(() {
          _errorMessage = res['message'] ?? 'Gagal mengirim laporan';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Lapor Aktivitas Mandiri', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header info
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.planName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A))),
                  const SizedBox(height: 4),
                  Text('Tanggal Pelaksanaan: ${widget.scheduledDate}', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                ],
              ),
            ),

            const SizedBox(height: 20),

            if (_errorMessage != null)
              Container(
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF1F2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(_errorMessage!, style: const TextStyle(fontSize: 12, color: Color(0xFFE11D48))),
              ),

            const Text('Status Pelaksanaan:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
            const SizedBox(height: 10),

            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () => setState(() => _executionStatus = 'dilaksanakan'),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      decoration: BoxDecoration(
                        color: _executionStatus == 'dilaksanakan' ? const Color(0xFFD1FAE5) : Colors.white,
                        border: Border.all(
                          color: _executionStatus == 'dilaksanakan' ? const Color(0xFF059669) : const Color(0xFFE2E8F0),
                          width: 2,
                        ),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Icon(Icons.check_circle_rounded, color: _executionStatus == 'dilaksanakan' ? const Color(0xFF059669) : const Color(0xFF94A3B8)),
                          const SizedBox(height: 6),
                          Text(
                            'Dilaksanakan',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                              color: _executionStatus == 'dilaksanakan' ? const Color(0xFF065F46) : const Color(0xFF475569),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: InkWell(
                    onTap: () => setState(() => _executionStatus = 'tidak_dilaksanakan'),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      decoration: BoxDecoration(
                        color: _executionStatus == 'tidak_dilaksanakan' ? const Color(0xFFFFE4E6) : Colors.white,
                        border: Border.all(
                          color: _executionStatus == 'tidak_dilaksanakan' ? const Color(0xFFE11D48) : const Color(0xFFE2E8F0),
                          width: 2,
                        ),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Icon(Icons.cancel_rounded, color: _executionStatus == 'tidak_dilaksanakan' ? const Color(0xFFE11D48) : const Color(0xFF94A3B8)),
                          const SizedBox(height: 6),
                          Text(
                            'Tidak Dilaksanakan',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                              color: _executionStatus == 'tidak_dilaksanakan' ? const Color(0xFF9F1239) : const Color(0xFF475569),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            const Text('Catatan Mandiri Pasien (Opsional):', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
            const SizedBox(height: 8),

            TextField(
              controller: _notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Contoh: Tidur pukul 21.45, kondisi rileks...',
                hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.all(14),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
              ),
            ),

            const SizedBox(height: 12),
            const Text(
              '* Laporan ini merupakan pelaporan mandiri untuk evaluasi pemulihan oleh terapis klinik.',
              style: TextStyle(fontSize: 11, color: Color(0xFF64748B), fontStyle: FontStyle.italic),
            ),

            const SizedBox(height: 28),

            ElevatedButton(
              onPressed: _isSubmitting ? null : _submitReport,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 2,
              ),
              child: _isSubmitting
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Kirim Laporan Aktivitas', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}
