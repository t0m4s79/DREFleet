import React, { useEffect, useState } from 'react';
import { Autocomplete, TextField, Grid, Typography, Input } from '@mui/material';
import InputMask from 'react-input-mask';

const validCodes = ['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M'];


const LicenseNumberInput = ({ value, onChange }) => {
    const [selectedCode, setSelectedCode] = useState('');
    const [numberPart, setNumberPart] = useState('');

    // Sync with the parent form state if `value` changes
    useEffect(() => {
        if (value) {
            const [code, number] = value.split('-');
            setSelectedCode(code);
            setNumberPart(number || '');
        }
    }, [value]);

    const handleCodeChange = (event, value) => {
        setSelectedCode(value);
        onChange(`${value}-${numberPart}`);
    };

    const handleNumberChange = (e) => {
        const newNumberPart = e.target.value;
        setNumberPart(newNumberPart);
        onChange(`${selectedCode}-${newNumberPart}`);
    };

    const onLicenseChange = () => {
        const licenseNumber = `${selectedCode}-${numberPart}`
        return licenseNumber
    }

    return (
        <div>
            <Typography >Carta de Condução</Typography>
            <Grid container spacing={2} style={{ marginTop: 2}}>
                    <Grid item xs={5}>
                        <Autocomplete
                            options={validCodes}
                            value={selectedCode}
                            onChange={handleCodeChange}
                            renderInput={(params) => <TextField {...params} label="Código identificador de região" />}
                        />
                    </Grid>

                    <Grid item xs={7}>
                        <InputMask
                            mask="999999 9"
                            value={numberPart}
                            onChange={handleNumberChange}
                            maskChar=''
                        >
                            {(inputProps) => <TextField {...inputProps} label="Dígitos" />}
                        </InputMask>
                    </Grid>
            </Grid>
        </div>
    );
}

export default LicenseNumberInput;
