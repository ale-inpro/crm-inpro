USE crm_inpro;

-- Ampliar ENUM resultado y permitir NULL en visitas programadas
ALTER TABLE visitas
  MODIFY resultado ENUM(
    'interesado',
    'muy_interesado',
    'neutral',
    'reagendar',
    'sin_interes',
    'no_interes',
    'pendiente'
  ) NULL DEFAULT NULL;