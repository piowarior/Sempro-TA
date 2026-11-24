import 'package:flutter/material.dart';
import 'report_activity_screen.dart';

class ActivityDetailScreen extends StatelessWidget {
  final Map<String, dynamic> plan;

  const ActivityDetailScreen({super.key, required this.plan});

  @override
  Widget build(BuildContext context) {
    final schedules = plan['schedules'] as List<dynamic>? ?? [];
    final reports = plan['reports'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(plan['name'] ?? 'Detail Aktivitas', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Plan Info Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    plan['name'] ?? 'Instruksi Mandiri',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    plan['description'] ?? '',
                    style: const TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.4),
                  ),
                  const SizedBox(height: 14),
                  Divider(color: Colors.grey.shade200),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _DetailMeta(label: 'Periode', value: '${plan['start_date']} s/d ${plan['end_date']}'),
                      _DetailMeta(label: 'Waktu Pengingat', value: plan['reminder_time'] ?? '-'),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Jadwal & Laporan
            const Text('Jadwal Pelaksanaan & Laporan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
            const SizedBox(height: 10),

            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: schedules.length,
              itemBuilder: (context, index) {
                final schedule = schedules[index];
                final schedDate = schedule['scheduled_date'] as String? ?? '';
                final status = schedule['status'] ?? 'pending';

                // Find matching report for this schedule
                final matchingReport = reports.firstWhere(
                  (r) => r['activity_schedule_id'] == schedule['id'] || r['activity_date'] == schedDate,
                  orElse: () => null,
                );

                return Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: matchingReport != null
                              ? (matchingReport['execution_status'] == 'dilaksanakan' ? const Color(0xFFD1FAE5) : const Color(0xFFFFE4E6))
                              : const Color(0xFFF1F5F9),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          matchingReport != null
                              ? (matchingReport['execution_status'] == 'dilaksanakan' ? Icons.check : Icons.close)
                              : Icons.schedule,
                          color: matchingReport != null
                              ? (matchingReport['execution_status'] == 'dilaksanakan' ? const Color(0xFF059669) : const Color(0xFFE11D48))
                              : const Color(0xFF64748B),
                          size: 16,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(schedDate, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                            const SizedBox(height: 2),
                            if (matchingReport != null)
                              Text(
                                'Status: ${matchingReport['execution_status']}' +
                                    (matchingReport['patient_notes'] != null && (matchingReport['patient_notes'] as String).isNotEmpty
                                        ? ' ("${matchingReport['patient_notes']}")'
                                        : ''),
                                style: TextStyle(
                                  fontSize: 11,
                                  color: matchingReport['execution_status'] == 'dilaksanakan' ? const Color(0xFF059669) : const Color(0xFFE11D48),
                                ),
                              )
                            else
                              Text('Pengingat: ${schedule['reminder_time']} &bull; Status: $status', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          ],
                        ),
                      ),
                      if (matchingReport == null && status != 'cancelled')
                        ElevatedButton(
                          onPressed: () {
                            final nav = Navigator.of(context);
                            nav.push(
                              MaterialPageRoute(
                                builder: (_) => ReportActivityScreen(
                                  planId: plan['id'],
                                  planName: plan['name'] ?? 'Aktivitas',
                                  scheduleId: schedule['id'],
                                  scheduledDate: schedDate,
                                ),
                              ),
                            ).then((val) {
                              if (val == true) nav.pop();
                            });
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF059669),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Text('Lapor', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _DetailMeta extends StatelessWidget {
  final String label;
  final String value;

  const _DetailMeta({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 10, color: Color(0xFF64748B))),
        const SizedBox(height: 2),
        Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
      ],
    );
  }
}
