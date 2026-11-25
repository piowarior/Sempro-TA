import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class VisitsScreen extends StatefulWidget {
  const VisitsScreen({super.key});

  @override
  State<VisitsScreen> createState() => _VisitsScreenState();
}

class _VisitsScreenState extends State<VisitsScreen> {
  late Future<Map<String, dynamic>> _visitsFuture;

  @override
  void initState() {
    super.initState();
    _loadVisits();
  }

  void _loadVisits() {
    setState(() {
      _visitsFuture = ApiService.getVisits();
    });
  }

  void _showBookingDialog(int nextMeeting) {
    DateTime selectedDate = DateTime.now().add(const Duration(days: 7));
    String selectedSlot = '09:00 - 11:00';
    final notesController = TextEditingController();
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                top: 24,
                left: 24,
                right: 24,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Ajukan Pertemuan #$nextMeeting',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1E293B),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: Color(0xFF64748B)),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Pilih tanggal dan sesi waktu yang Anda inginkan untuk konsultasi dan evaluasi MHLS.',
                      style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Tanggal Kunjungan',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF334155)),
                    ),
                    const SizedBox(height: 8),
                    InkWell(
                      onTap: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: selectedDate,
                          firstDate: DateTime.now(),
                          lastDate: DateTime.now().add(const Duration(days: 120)),
                        );
                        if (picked != null) {
                          setModalState(() => selectedDate = picked);
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        decoration: BoxDecoration(
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                          borderRadius: BorderRadius.circular(10),
                          color: const Color(0xFFF8FAFC),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.calendar_month_rounded, size: 20, color: Color(0xFF059669)),
                            const SizedBox(width: 10),
                            Text(
                              DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(selectedDate),
                              style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Pilihan Jam / Sesi',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF334155)),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: ['09:00 - 11:00', '13:00 - 15:00', '15:30 - 17:30'].map((slot) {
                        final isSelected = selectedSlot == slot;
                        return ChoiceChip(
                          label: Text(slot),
                          selected: isSelected,
                          selectedColor: const Color(0xFFD1FAE5),
                          labelStyle: TextStyle(
                            color: isSelected ? const Color(0xFF065F46) : const Color(0xFF475569),
                            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                            fontSize: 12,
                          ),
                          onSelected: (val) {
                            if (val) setModalState(() => selectedSlot = slot);
                          },
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Catatan Tambahan (Opsional)',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF334155)),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: notesController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        hintText: 'Contoh: Mohon jam 9 tepat karena ada keperluan...',
                        hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                        ),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          elevation: 0,
                        ),
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                setModalState(() => isSubmitting = true);
                                final formattedDate = DateFormat('yyyy-MM-dd').format(selectedDate);
                                final result = await ApiService.requestBooking(
                                  meetingNumber: nextMeeting,
                                  visitDate: formattedDate,
                                  timeSlot: selectedSlot,
                                  patientNotes: notesController.text.trim(),
                                );
                                setModalState(() => isSubmitting = false);
                                if (!context.mounted) return;
                                Navigator.pop(context);

                                if (result['success'] == true) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      content: Text('Jadwal berhasil diajukan, menunggu konfirmasi klinik'),
                                      backgroundColor: Color(0xFF059669),
                                    ),
                                  );
                                  _loadVisits();
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(result['message'] ?? 'Gagal mengajukan jadwal'),
                                      backgroundColor: Colors.red,
                                    ),
                                  );
                                }
                              },
                        child: isSubmitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : const Text('Kirim Pengajuan Jadwal', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Jadwal Kunjungan MHLS',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF0F172A)),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _visitsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: Color(0xFF059669)));
          }

          if (snapshot.hasError) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Color(0xFFEF4444)),
                  const SizedBox(height: 12),
                  Text('Terjadi kesalahan: ${snapshot.error}', style: const TextStyle(color: Color(0xFF64748B))),
                  const SizedBox(height: 16),
                  ElevatedButton(onPressed: _loadVisits, child: const Text('Coba Lagi')),
                ],
              ),
            );
          }

          final data = snapshot.data!;
          final patient = data['patient'] as Map<String, dynamic>? ?? {};
          final activeVisit = data['active_visit'] as Map<String, dynamic>?;
          final visits = (data['visits'] as List<dynamic>? ?? []);

          final completedMeetings = patient['completed_meetings'] ?? 0;
          final totalMeetings = patient['total_program_meetings'] ?? 12;
          final nextMeetingNumber = completedMeetings + 1;

          return RefreshIndicator(
            onRefresh: () async => _loadVisits(),
            color: const Color(0xFF059669),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Program Progress Card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.02),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Program Pendampingan 3 Bulan',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E293B)),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFECFDF5),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '$completedMeetings dari $totalMeetings Selesai',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF059669),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(6),
                        child: LinearProgressIndicator(
                          value: totalMeetings > 0 ? (completedMeetings / totalMeetings) : 0,
                          backgroundColor: const Color(0xFFF1F5F9),
                          valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                          minHeight: 8,
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Color(0xFF059669)),
                            foregroundColor: const Color(0xFF059669),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          icon: const Icon(Icons.add_circle_outline, size: 18),
                          label: Text(
                            'Ajukan Booking Pertemuan #$nextMeetingNumber',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          onPressed: () => _showBookingDialog(nextMeetingNumber),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // Active / Next Confirmed Visit Card
                if (activeVisit != null) ...[
                  const Text(
                    'Jadwal Terdekat',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF065F46), Color(0xFF047857)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF059669).withOpacity(0.2),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                'Pertemuan #${activeVisit['meeting_number']}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFA7F3D0),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'TERKONFIRMASI',
                                style: TextStyle(
                                  color: Color(0xFF065F46),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 10,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            const Icon(Icons.event, color: Colors.white70, size: 18),
                            const SizedBox(width: 8),
                            Text(
                              activeVisit['visit_date'] != null
                                  ? activeVisit['visit_date'].toString().substring(0, 10)
                                  : '-',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            const SizedBox(width: 16),
                            const Icon(Icons.access_time, color: Colors.white70, size: 18),
                            const SizedBox(width: 8),
                            Text(
                              activeVisit['time_slot'] ?? '-',
                              style: const TextStyle(color: Colors.white, fontSize: 14),
                            ),
                          ],
                        ),
                        if (activeVisit['staff_notes'] != null) ...[
                          const SizedBox(height: 12),
                          Text(
                            'Catatan Klinik: ${activeVisit['staff_notes']}',
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // All Visits Timeline List
                const Text(
                  'Riwayat & Pengajuan Kunjungan',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                ),
                const SizedBox(height: 10),

                if (visits.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: const Center(
                      child: Text(
                        'Belum ada riwayat kunjungan.',
                        style: TextStyle(color: Color(0xFF94A3B8)),
                      ),
                    ),
                  )
                else
                  for (final v in visits) ...[
                    Builder(builder: (context) {
                      final status = v['status'] as String? ?? 'pending';
                      Color badgeColor;
                      Color badgeTextColor;
                      String statusText;

                      switch (status) {
                        case 'completed':
                          badgeColor = const Color(0xFFDCFCE7);
                          badgeTextColor = const Color(0xFF15803D);
                          statusText = 'Selesai Dilayani';
                          break;
                        case 'confirmed':
                          badgeColor = const Color(0xFFE0E7FF);
                          badgeTextColor = const Color(0xFF4338CA);
                          statusText = 'Terkonfirmasi';
                          break;
                        case 'cancelled':
                          badgeColor = const Color(0xFFFEE2E2);
                          badgeTextColor = const Color(0xFFB91C1C);
                          statusText = 'Dibatalkan';
                          break;
                        default:
                          badgeColor = const Color(0xFFFEF3C7);
                          badgeTextColor = const Color(0xFFB45309);
                          statusText = 'Menunggu Konfirmasi';
                      }

                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Center(
                                child: Text(
                                  '#${v['meeting_number'] ?? '-'}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF0F172A),
                                    fontSize: 14,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        v['visit_date'] != null
                                            ? v['visit_date'].toString().substring(0, 10)
                                            : '-',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                          color: Color(0xFF1E293B),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: badgeColor,
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Text(
                                          statusText,
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: badgeTextColor,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      const Icon(Icons.schedule, size: 14, color: Color(0xFF64748B)),
                                      const SizedBox(width: 4),
                                      Text(
                                        v['time_slot'] ?? '-',
                                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                      ),
                                    ],
                                  ),
                                  if (v['staff_notes'] != null && v['staff_notes'].toString().isNotEmpty) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      v['staff_notes'],
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                  ],
              ],
            ),
          );
        },
      ),
    );
  }
}
